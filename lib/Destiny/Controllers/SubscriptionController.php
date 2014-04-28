<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\PayPalApiService;
use Destiny\Commerce\PaymentProfileStatus;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Application;
use Destiny\Commerce\OrderStatus;
use Destiny\Common\Utils\Date;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Commerce\PaymentStatus;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;

/**
 * @Controller
 */
class SubscriptionController {
  
    /**
     * @Route ("/subscribe")
     *
     * Build subscribe checkout form
     *
     * @param array $params         
     */
    public function subscribe(array $params, ViewModel $model) {
        $subscriptionsService = SubscriptionsService::instance ();
        
        if(Session::hasRole(UserRole::USER)){
            $userId = Session::getCredentials ()->getUserId ();
            
            // Pending subscription
            $subscription = $subscriptionsService->getUserPendingSubscription ( $userId );
            if (! empty ( $subscription )) {
                throw new Exception ( 'You already have a subscription in the "pending" state.' );
            }
            
            // Active subscription
            $model->subscription = $subscriptionsService->getUserActiveSubscription ( $userId );
        }
        
        $model->title = 'Subscribe';
        $model->subscriptions = Config::$a ['commerce'] ['subscriptions'];
        return 'subscribe';
    }
    
    /**
     * @Route ("/subscription/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionCancel(array $params, ViewModel $model) {
        $subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
        if (empty ( $subscription )) {
            throw new Exception ( 'Must have an active subscription' );
        }
        $model->subscription = $subscription;
        return 'profile/cancelsubscription';
    }
    
    /**
     * @Route ("/subscription/{id}/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionGiftCancel(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'id');

        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();

        $userId = Session::getCredentials ()->getUserId ();
        $subscription = $subscriptionsService->getActiveSubscriptionByIdAndGifterId ( $params['id'], $userId );
        $giftee = $userService->getUserById ( $subscription['userId'] );

        if(empty($subscription)){
            throw new Exception ( 'Invalid subscription' );
        }

        $model->subscription = $subscription;
        $model->giftee = $giftee;
        return 'profile/cancelsubscription';
    }
    
    /**
     * @Route ("/subscription/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     * @Transactional
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws Exception
     * @return string
     */
    public function subscriptionCancelProcess(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'subscriptionId');

        $ordersService = OrdersService::instance ();
        $payPalAPIService = PayPalApiService::instance ();
        $subscriptionsService = SubscriptionsService::instance();
        $authenticationService = AuthenticationService::instance();
        
        $userId = Session::getCredentials ()->getUserId ();
        $subscription = $subscriptionsService->getSubscriptionById ( $params['subscriptionId'] );

        if(empty($subscription)){
           throw new Exception( 'Invalid subscription' );
        }

        if($subscription['userId'] != $userId && $subscription['gifter'] != $userId){
            throw new Exception( 'Invalid subscription owner' );
        }

        if($subscription['status'] != SubscriptionStatus::ACTIVE){
           throw new Exception( 'Invalid subscription status' );
        }

        // Cancel the payment profile
        if (! empty ( $subscription ['paymentProfileId'] )) {
            $paymentProfile = $ordersService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
            if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVEPROFILE ) === 0) {
               $payPalAPIService->cancelPaymentProfile ( $subscription, $paymentProfile );
            }
        }
        
        if(isset($params['cancelRemainingTime']) && $params['cancelRemainingTime'] == '1' && empty($subscription['gifter'])){
            $subscriptionsService->updateSubscriptionState ( $subscription ['subscriptionId'], $subscription ['status'] );
            $authenticationService->flagUserForUpdate ( $userId );
            $subscription ['status'] = SubscriptionStatus::CANCELLED;
        }

        $subscription ['recurring'] = false;
        $subscriptionsService->updateSubscriptionRecurring ( $subscription ['subscriptionId'], false );

        $model->subscription = $subscription;
        $model->subscriptionCancelled = true;
        return 'profile/cancelsubscription';
    }
    
    
    /**
     * @Route ("/subscription/{orderId}/error")
     * @Secure ({"USER"})
     *
     * @param array $params
     */
    public function subscriptionError(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'orderId');
      
        // @TODO make this more solid
        $userId = Session::getCredentials ()->getUserId ();
        $ordersService = OrdersService::instance ();
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
      
        if (empty ( $order )) {
            throw new Exception ( 'Subscription failed' );
        }
      
        $model->order = $order;
        $model->orderId = $params ['orderId'];
        return 'order/ordererror';
    }
    
    /**
     * @Route ("/subscription/confirm")
     *
     * Create and send the order
     *
     * @param array $params
     */
    public function subscriptionConfirm(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'subscription');

        $subscriptionsService = SubscriptionsService::instance ();
        
        // If there is no user, save the selection, and go to the login screen
        if (! Session::hasRole ( UserRole::USER )) {
            $url = '/subscription/confirm?subscription=' . $params ['subscription'];
            if(isset($params ['gift']) && !empty($params ['gift'])){
               $url .= '&gift=' . $params ['gift'];
            }
            return 'redirect: /login?follow=' . urlencode( $url );
        }
      
        $userId = Session::getCredentials ()->getUserId ();
        $subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
        
        if(empty($subscriptionType)){
            throw new Exception('Invalid subscription specified');
        }
      
        // If this is a gift, there is no need to check the current subscription
        if(isset($params['gift']) && !empty($params['gift'])){

          $model->gift = $params['gift'];

        }else{

          // Existing subscription
          $currentSubscription = $subscriptionsService->getUserActiveSubscription ( $userId );
          if (! empty ( $currentSubscription )) {
             $model->currentSubscription = $currentSubscription;
             $model->currentSubscriptionType = $subscriptionsService->getSubscriptionType ( $currentSubscription ['subscriptionType'] );
             
             // Warn about identical subscription overwrite
             if($model->currentSubscriptionType['id'] == $subscriptionType ['id']){
                $model->warning = new Exception('you are about to overwrite your existing subscription with a duplicate one.');
             }
             
          }

        }

        $model->subscriptionType = $subscriptionType;
        return 'order/orderconfirm';
    }
    
    /**
     * @Route ("/subscription/create")
     * @Secure ({"USER"})
     * @Transactional
     *
     * Create and send the order
     *
     * @param array $params
     */
    public function subscriptionCreate(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'subscription');
        
        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $ordersService = OrdersService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        
        $userId = Session::getCredentials ()->getUserId ();
        $subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
        $recurring = (isset ( $params ['renew'] ) && $params ['renew'] == '1');
        $giftReceiverUsername = (isset( $params['gift'] ) && !empty( $params['gift'] )) ? $params['gift'] : null;
        $giftReceiver = null;

        try {

            if(!empty($giftReceiverUsername)){
                // make sure the receiver is valid
                $giftReceiver = $userService->getUserByUsername( $giftReceiverUsername );
                if(empty($giftReceiver)){
                   throw new Exception ( 'Invalid giftee' );
                }
                if(!$subscriptionsService->getCanUserReceiveGift ( $userId, $giftReceiver['userId'], null )){
                   throw new Exception ( 'Invalid giftee, cannot accept gifts' );
                }
            }

            // Create NEW order
            $order = $ordersService->createSubscriptionOrder ( $subscriptionType, $userId );

            // Create the subscription
            $start = Date::getDateTime ();
            $end = Date::getDateTime ();
            $end->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );

            $subscription = array (
              'userId'             => $userId,
              'orderId'            => $order ['orderId'],
              'subscriptionSource' => Config::$a ['subscriptionType'],
              'subscriptionType'   => $subscriptionType ['id'],
              'subscriptionTier'   => $subscriptionType ['tier'],
              'createdDate'        => $start->format ( 'Y-m-d H:i:s' ),
              'endDate'            => $end->format ( 'Y-m-d H:i:s' ),
              'recurring'          => 0,
              'status'             => SubscriptionStatus::_NEW
            );

            // If this is a gift, change the user and the gifter
            if(!empty($giftReceiver)){
                $subscription['userId'] = $giftReceiver['userId'];
                $subscription['gifter'] = $userId;
            }

            // Insert subscription
            $subscriptionId = $subscriptionsService->addSubscription ( $subscription );

            // Add payment profile
            $paymentProfile = null;
            if ($recurring) {
                $billingStartDate = Date::getDateTime ( date ( 'm/d/y' ) );
                $billingStartDate->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );
                $paymentProfile = $ordersService->createPaymentProfile ( $userId, $order, $subscriptionType, $billingStartDate );
            }
            
            $setECResponse = $payPalApiService->createECResponse ( '/subscription/process', $order, $subscriptionType, $recurring );
            if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
               throw new Exception ( $setECResponse->Errors->ShortMessage );
            }
            return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );
          
        }catch (Exception $e){

            if (! empty ( $order ))
                $ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
            if (! empty ( $paymentProfile ))
                $ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
            if (! empty ( $subscriptionId ))
                $subscriptionsService->updateSubscriptionState ( $subscriptionId, SubscriptionStatus::ERROR );

            $log = Application::instance ()->getLogger ();
            $log->error ( $e->getMessage(), $order );
            return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
        }
    }
    
    /**
     * @Route ("/subscription/{orderId}/complete")
     * @Secure ({"USER"})
     * @Transactional
     *
     * @param array $params
     */
    public function subscriptionComplete(array $params, ViewModel $model) {
        FilterParams::isRequired($params, 'orderId');
        
        $ordersService = OrdersService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();
      
        $userId = Session::getCredentials ()->getUserId ();
      
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
        if (empty ( $order )) {
           throw new Exception ( sprintf ( 'Invalid order record orderId:%s userId:%s', $params ['orderId'], $userId ) );
        }
      
        $subscription = $subscriptionsService->getSubscriptionByOrderId ( $order ['orderId'] );
        // Make sure the order is assigned to this user, or at least they are the gifter
        if( empty ( $subscription ) || ($subscription['userId'] != $userId && $subscription['gifter'] != $userId) ){
            throw new Exception ( 'Invalid subscription record' );
        }

        // Load the giftee
        if(!empty($subscription['gifter'])){
            $giftee = $userService->getUserById ( $subscription['userId'] );
            $model->giftee = $giftee;
        }

        $subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
        $paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
      
        // Show the order complete screen
        $model->order = $order;
        $model->subscription = $subscription;
        $model->subscriptionType = $subscriptionType;
        $model->paymentProfile = $paymentProfile;
        return 'order/ordercomplete';
    }
    
    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     * @Transactional
     *
     * We were redirected here from PayPal after the buyer approved/cancelled the payment
     *
     * @param array $params
     */
    public function subscriptionProcess(array $params, ViewModel $model) {

        FilterParams::isRequired ( $params, 'orderId' );
        FilterParams::isRequired ( $params, 'token' );
        FilterParams::isThere ( $params, 'success' );
          
        $ordersService = OrdersService::instance ();
        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        $chatIntegrationService = ChatIntegrationService::instance ();
        $authenticationService = AuthenticationService::instance ();
        
        $userId = Session::getCredentials ()->getUserId ();
        
        // Get the order
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
        if (empty ( $order ) || strcasecmp($order ['state'], OrderStatus::_NEW) !== 0) {
            throw new Exception ( 'Invalid order record' );
        }
          
        try {

            // If we got a failed response URL
            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
                throw new Exception ( 'Order request failed' );
            }
            
            // Get the subscription from the order
            $orderSubscription = $subscriptionsService->getSubscriptionByOrderId ( $order ['orderId'] );
            $subscriptionUser =  $userService->getUserById ( $orderSubscription['userId'] );

            // Make sure the subscription is valid
            if (empty ( $orderSubscription )) {
                throw new Exception ( 'Invalid order subscription' );
            }

            // Make sure the subscription is either owned or gifted by the user
            if($subscriptionUser['userId'] != $userId && $subscriptionUser['gifter'] != $userId){
                throw new Exception ( 'Invalid order subscription' );
            }
            
            $subscriptionType = $subscriptionsService->getSubscriptionType ( $orderSubscription ['subscriptionType'] );
            $paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
            
            // Get the checkout info
            $ecResponse = $payPalApiService->retrieveCheckoutInfo ( $params ['token'] );
            if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success') {
                throw new Exception ( 'Failed to retrieve express checkout details' );
            }
            
            // Moved this down here, as if the order status is error, the payerID is not returned
            FilterParams::isRequired ( $params, 'PayerID' );
            
            // Point of no return - we only every want a person to get here if their order was a successful sequence
            Session::set ( 'token' );
            Session::set ( 'orderId' );
            
            // Recurring payment
            if (! empty ( $paymentProfile )) {
              $createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $params ['token'], $subscriptionType );
              if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success') {
                 throw new Exception ( 'Failed to create recurring payment request' );
              }
              $paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
              $paymentStatus = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus;
              if (empty ( $paymentProfileId )) {
                  throw new Exception ( 'Invalid recurring payment profileId returned from Paypal' );
              }
              // Set the payment profile to active, and paymetProfileId
              $ordersService->updatePaymentProfileId ( $paymentProfile ['profileId'], $paymentProfileId, $paymentStatus );
            }
            
            // Complete the checkout
            $DoECResponse = $payPalApiService->getECPaymentResponse ( $params ['PayerID'], $params ['token'], $order );
            if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
                if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
                  $payPalApiService->recordECPayments ( $DoECResponse, $params ['PayerID'], $order );
                  $ordersService->updateOrderState ( $order ['orderId'], $order ['state'] );
                } else {
                    throw new Exception ( 'No payments for express checkout order' );
                }
            } else {
                throw new Exception ( $DoECResponse->Errors [0]->LongMessage );
            }

            // If the user already has a subscription and ONLY if this subscription was NOT a gift
            if(!isset($orderSubscription ['gifter']) || empty($orderSubscription ['gifter'])){

                $activeSubscription = $subscriptionsService->getUserActiveSubscription ( $subscriptionUser['userId'] );
                if (! empty ( $activeSubscription )) {

                    // Cancel any attached payment profiles
                    $ordersService = OrdersService::instance ();
                    $paymentProfile = $ordersService->getPaymentProfileById ( $activeSubscription ['paymentProfileId'] );
                    if (! empty ( $paymentProfile )) {
                      $payPalApiService->cancelPaymentProfile ( $activeSubscription, $paymentProfile );
                      $subscriptionsService->updateSubscriptionRecurring ( $activeSubscription ['subscriptionId'], false );
                    }

                    // Cancel the active subscription
                    $subscriptionsService->updateSubscriptionState ( $activeSubscription ['subscriptionId'], SubscriptionStatus::CANCELLED );
                }
            }

            // Update the subscription status
            $subscriptionsService->updateSubscriptionState ( $orderSubscription ['subscriptionId'], SubscriptionStatus::ACTIVE );
            $subscriptionsService->updateSubscriptionPaymentProfile ( $orderSubscription ['subscriptionId'], $paymentProfile ['profileId'], true );

            // Unban the user if a ban is found
            $ban = $userService->getUserActiveBan ( $subscriptionUser['userId'] );
            // only unban the user if the ban is non-permanent
            // we unban the user if no ban is found because it also unmutes
            if (empty ( $ban ) || empty( $ban ['endtimestamp'] )) {
               $chatIntegrationService->sendUnban ( $subscriptionUser['userId'] );
            }

            // Flag the user for 'update'
            $authenticationService->flagUserForUpdate ( $subscriptionUser['userId'] );

            // Handle the subscription broadcast
            $randomEmote = Config::$a['chat']['customemotes'][ array_rand ( Config::$a['chat']['customemotes'] ) ];
            $chatIntegrationService->sendBroadcast ( sprintf ( "%s has just become a %s subscriber! %s", $subscriptionUser['username'], $subscriptionType ['tierLabel'], $randomEmote ) );

            // Redirect to completion page
            return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/complete';
          
        }catch (Exception $e){

            if (! empty ( $order ))
              $ordersService->updateOrderState ( $order ['orderId'], OrderStatus::ERROR );
            if (! empty ( $paymentProfile ))
              $ordersService->updatePaymentStatus ( $paymentProfile ['paymentId'], PaymentStatus::ERROR );
            if (! empty ( $orderSubscription ))
              $subscriptionsService->updateSubscriptionState ( $orderSubscription['subscriptionId'], SubscriptionStatus::ERROR );

            $log = Application::instance ()->getLogger ();
            $log->error ( $e->getMessage(), $order );

            return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
        }
    }
    
    /**
     * Check if a user can receive a gift
     * Returns JSON 
     *
     * @Route ("/gift/check")
     * @Secure ({"USER"})
     *
     * @param array $params
     */
    public function giftCheckUser(array $params, ViewModel $model) {
      FilterParams::isRequired($params, 's');

      $userService = UserService::instance ();
      $subscriptionService = SubscriptionsService::instance();
      $userId = Session::getCredentials ()->getUserId ();

      $data = array(
        'valid'    => false,
        'cangift'  => false,
        'username' => $params ['s']
      );

      $user = $userService->getUserByUsername( $params ['s'] );
      if(!empty($user)){
          $data['cangift'] = $subscriptionService->getCanUserReceiveGift ( $userId, $user['userId'], null );
          $data['valid']   = true;
      }

      $response = new Response ( Http::STATUS_OK );
      $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
      $response->setBody ( json_encode ( $data ) );
      return $response;
    }
  
}