<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
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
     * @param ViewModel $model
     * @return string
     * @throws Exception
     */
    public function subscribe(ViewModel $model) {
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
     * @param ViewModel $model
     * @throws Exception
     * @return string
     */
    public function subscriptionCancel(ViewModel $model) {
        $subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
        if (empty ( $subscription )) {
            throw new Exception ( 'Must have an active subscription' );
        }
        $model->subscription = $subscription;
        $model->title = 'Cancel Subscription';
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
        FilterParams::required($params, 'id');

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
        $model->title = 'Cancel Subscription';
        return 'profile/cancelsubscription';
    }
    
    /**
     * @Route ("/subscription/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     *
     * @param array $params         
     * @param ViewModel $model          
     * @throws \Exception
     * @return string
     */
    public function subscriptionCancelProcess(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscriptionId');

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

        $log = Application::instance()->getLogger();
        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {

            // Cancel the payment profile
            if (! empty ( $subscription ['paymentProfileId'] )) {
                $paymentProfile = $ordersService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
                if (strcasecmp ( $paymentProfile ['state'], PaymentProfileStatus::ACTIVE_PROFILE ) === 0) {
                    $payPalAPIService->cancelPaymentProfile ( $paymentProfile );
                    $ordersService->updatePaymentProfile (array(
                        'profileId' => $paymentProfile ['profileId'],
                        'state' => PaymentProfileStatus::CANCELLED_PROFILE
                    ));
                }
            }

            // Update subscription
            if(isset($params['cancelRemainingTime']) && $params['cancelRemainingTime'] == '1'){
                $subscription ['status'] = SubscriptionStatus::CANCELLED;
                $subscriptionsService->updateSubscription (array(
                    'subscriptionId' => $subscription ['subscriptionId'],
                    'status' => $subscription ['status']
                ));
            }

            $authenticationService->flagUserForUpdate ( $subscription ['userId'] );
            $conn->commit();
        } catch ( \Exception $e ) {
            $log->critical("Error cancelling subscription", $subscription);
            $conn->rollBack();
            throw $e;
        }
        
        $model->subscription = $subscription;
        $model->subscriptionCancelled = true;
        $model->title = 'Cancel Subscription';
        return 'profile/cancelsubscription';
    }

    /**
     * @Route ("/subscription/{orderId}/error")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionError(array $params, ViewModel $model) {
        FilterParams::required($params, 'orderId');
      
        // @TODO make this more solid
        $userId = Session::getCredentials ()->getUserId ();
        $ordersService = OrdersService::instance ();
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );
      
        if (empty ( $order )) {
            throw new Exception ( 'Subscription failed' );
        }
      
        $model->order = $order;
        $model->orderId = $params ['orderId'];
        $model->title = 'Subscription Error';
        return 'order/ordererror';
    }

    /**
     * @Route ("/subscription/confirm")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionConfirm(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscription');

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
          $model->warning = new Exception('If the giftee has a subscription by the time this payment is completed the subscription will be marked as failed, but your payment will still go through.');

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
        $model->title = 'Subscription Confirm';
        return 'order/orderconfirm';
    }

    /**
     * @Route ("/subscription/create")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws \Exception
     */
    public function subscriptionCreate(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscription');
        
        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $ordersService = OrdersService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        $log = Application::instance ()->getLogger ();
        
        $userId = Session::getCredentials ()->getUserId ();
        $subscriptionType = $subscriptionsService->getSubscriptionType ( $params ['subscription'] );
        $recurring = (isset ( $params ['renew'] ) && $params ['renew'] == '1');
        $giftReceiverUsername = (isset( $params['gift'] ) && !empty( $params['gift'] )) ? $params['gift'] : null;
        $giftReceiver = null;

        if (isset( $params ['sub-message'] ) and !empty( $params ['sub-message'] ))
            Session::set('subMessage', mb_substr($params ['sub-message'], 0, 250));

        try {
            if(!empty($giftReceiverUsername)){
                $giftReceiver = $userService->getUserByUsername( $giftReceiverUsername );
                if(empty($giftReceiver)){
                   throw new Exception ( 'Invalid giftee (user not found)' );
                }
                if ($userId == $giftReceiver['userId']){
                   throw new Exception ( 'Invalid giftee (cannot gift yourself)' );
                }
                if(!$subscriptionsService->getCanUserReceiveGift ( $userId, $giftReceiver['userId'] )){
                   throw new Exception ( 'Invalid giftee (user does not accept gifts)' );
                }
            }
        }catch (Exception $e){
            $model->order = null;
            $model->orderId = null;
            $model->title = 'Subscription Error';
            return 'order/ordererror';
        }

        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {
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
                'recurring'          => ($recurring) ? 1:0,
                'status'             => SubscriptionStatus::_NEW
            );

            // If this is a gift, change the user and the gifter
            if(!empty($giftReceiver)){
                $subscription['userId'] = $giftReceiver['userId'];
                $subscription['gifter'] = $userId;
            }

            // Insert subscription
            $subscriptionsService->addSubscription ( $subscription );

            // Payment date is 1 day before subscription rolls over.
            if ($recurring) {
                $nextPaymentDate = Date::getDateTime ();
                $nextPaymentDate->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );
                $nextPaymentDate->modify( '-1 DAY' );
                $ordersService->createPaymentProfile ( $userId, $order, $subscriptionType, $nextPaymentDate );
            }

            // Send request to paypal
            $setECResponse = $payPalApiService->createECResponse ( '/subscription/process', $order, $subscriptionType, $recurring );
            if (empty ( $setECResponse ) || $setECResponse->Ack != 'Success') {
                throw new Exception ( $setECResponse->Errors->ShortMessage );
            }

            // Commit transaction and continue to paypal.
            $conn->commit();

            return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $setECResponse->Token );

        } catch ( \Exception $e ) {
            $log->critical("Error creating order");
            $conn->rollBack();
        }
    }

    /**
     * @Route ("/subscription/{orderId}/complete")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionComplete(array $params, ViewModel $model) {
        FilterParams::required($params, 'orderId');
        
        $ordersService = OrdersService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();
        $userId = Session::getCredentials ()->getUserId ();
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );

        if (empty ( $order ))
           throw new Exception ( sprintf ( 'Invalid order record orderId:%s userId:%s', $params ['orderId'], $userId ) );

        // Make sure the order is assigned to this user, or at least they are the gifter
        $subscription = $subscriptionsService->getSubscriptionByOrderId ( $order ['orderId'] );
        if( empty ( $subscription ) || ($subscription['userId'] != $userId && $subscription['gifter'] != $userId) )
            throw new Exception ( 'Invalid subscription record' );

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
        $model->title = 'Subscription Complete';
        return 'order/ordercomplete';
    }

    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     *
     * We were redirected here from PayPal after the buyer approved/cancelled the payment
     *
     * @param array $params
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     * TODO clean this method up
     */
    public function subscriptionProcess(array $params) {

        FilterParams::required ( $params, 'orderId' );
        FilterParams::required ( $params, 'token' );
        FilterParams::declared ( $params, 'success' );
          
        $ordersService = OrdersService::instance ();
        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        $chatIntegrationService = ChatIntegrationService::instance ();
        $authenticationService = AuthenticationService::instance ();
        $log = Application::instance ()->getLogger ();
        $userId = Session::getCredentials ()->getUserId ();
        $order = $ordersService->getOrderByIdAndUserId ( $params ['orderId'], $userId );

        if (empty ( $order ) || strcasecmp($order ['state'], OrderStatus::_NEW) !== 0)
            throw new Exception ( 'Invalid order record' );

        try {

            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false)
                throw new Exception ( 'Order request failed' );

            $orderSubscription = $subscriptionsService->getSubscriptionByOrderId ( $order ['orderId'] );
            if (empty ( $orderSubscription ))
                throw new Exception ( 'Invalid order subscription' );

            $subscriptionUser =  $userService->getUserById ( $orderSubscription['userId'] );
            if($subscriptionUser['userId'] != $userId && $orderSubscription['gifter'] != $userId)
                throw new Exception ( 'Invalid order subscription' );

            $subscriptionType = $subscriptionsService->getSubscriptionType ( $orderSubscription ['subscriptionType'] );
            $paymentProfile = $ordersService->getPaymentProfileByOrderId ( $order ['orderId'] );
            $ecResponse = $payPalApiService->retrieveCheckoutInfo ( $params ['token'] );
            if (! isset ( $ecResponse ) || $ecResponse->Ack != 'Success')
                throw new Exception ( 'Failed to retrieve express checkout details' );

            FilterParams::required ( $params, 'PayerID' ); // if the order status is an error, the payerID is not returned
            Session::set ( 'token' );
            Session::set ( 'orderId' );
            
            // Recurring payment
            if (! empty ( $paymentProfile )) {
                $createRPProfileResponse = $payPalApiService->createRecurringPaymentProfile ( $paymentProfile, $params ['token'], $subscriptionType );

                if (! isset ( $createRPProfileResponse ) || $createRPProfileResponse->Ack != 'Success')
                    throw new Exception ( 'Failed to create recurring payment request' );

                $paymentProfileId = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
                if (empty ( $paymentProfileId ))
                    throw new Exception ( 'Invalid recurring payment profileId returned from Paypal' );

                // todo: the profile state very rarely will NOT be ActiveProfile, need to refactor the logic later on to handle it
                $ordersService->updatePaymentProfile(array (
                    'profileId' => $paymentProfile['profileId'],
                    'paymentProfileId' => $paymentProfileId,
                    'state' => PaymentProfileStatus::ACTIVE_PROFILE
                ));

                $subscriptionsService->updateSubscription (array(
                    'subscriptionId' => $orderSubscription ['subscriptionId'],
                    'paymentProfileId' => $paymentProfile ['profileId']
                ));
            }

            // Record the payments as well as check if any are not in the completed state
            $orderStatus = OrderStatus::COMPLETED;
            $DoECResponse = $payPalApiService->getECPaymentResponse ( $params ['PayerID'], $params ['token'], $order );
            if (isset ( $DoECResponse ) && $DoECResponse->Ack == 'Success') {
                if (isset ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo )) {
                    for($i = 0; $i < count ( $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo ); ++ $i) {
                        $paymentInfo = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo [$i];
                        $payment = array ();
                        $payment ['orderId'] = $order ['orderId'];
                        $payment ['payerId'] = $params ['PayerID'];
                        $payment ['amount'] = $paymentInfo->GrossAmount->value;
                        $payment ['currency'] = $paymentInfo->GrossAmount->currencyID;
                        $payment ['transactionId'] = $paymentInfo->TransactionID;
                        $payment ['transactionType'] = $paymentInfo->TransactionType;
                        $payment ['paymentType'] = $paymentInfo->PaymentType;
                        $payment ['paymentStatus'] = $paymentInfo->PaymentStatus;
                        $payment ['paymentDate'] = Date::getDateTime ( $paymentInfo->PaymentDate )->format ( 'Y-m-d H:i:s' );
                        $ordersService->addOrderPayment ( $payment );
                        // TODO: clean-up this is strange logic -- paypal sends a "list" of payments - for us there is only one.
                        if ($paymentInfo->PaymentStatus != PaymentStatus::COMPLETED) {
                            $orderStatus = OrderStatus::PENDING;
                        }
                    }
                    $ordersService->updateOrder(array(
                        'orderId' => $order ['orderId'],
                        'state' => $orderStatus
                    ));
                } else {
                    throw new Exception ( 'No payments for express checkout order' );
                }
            } else {
                throw new Exception ( $DoECResponse->Errors [0]->LongMessage );
            }

            // Check if this is a gift, check that the giftee is still eligible
            // Update the state to ERROR
            if(!empty($orderSubscription['gifter']) && !$subscriptionsService->getCanUserReceiveGift ( $userId, $subscriptionUser['userId'] )){
                throw new Exception(sprintf('Duplicate subscription attempt, Gifter: %d GifteeId: %d, OrderId: %d', $userId, $subscriptionUser['userId'], $order ['orderId']));
            }

            // Activate subscription
            $subscriptionsService->updateSubscription (array(
                'subscriptionId' => $orderSubscription ['subscriptionId'],
                'status' => SubscriptionStatus::ACTIVE
            ));

            // only unban the user if the ban is non-permanent or the tier of the subscription is >= 2
            // we unban the user if no ban is found because it also unmutes
            $ban = $userService->getUserActiveBan ( $subscriptionUser['userId'] );
            if (empty ( $ban ) or ( !empty( $ban ['endtimestamp'] ) or $orderSubscription['subscriptionTier'] >= 2 ) ) {
               $chatIntegrationService->sendUnban ( $subscriptionUser['userId'] );
            }
            $authenticationService->flagUserForUpdate ( $subscriptionUser['userId'] );

            // Broadcast
            $randomEmote = Config::$a['chat']['customemotes'][ array_rand ( Config::$a['chat']['customemotes'] ) ];
            if(!empty($orderSubscription['gifter'])){
                $gifter   = $userService->getUserById( $orderSubscription['gifter'] );
                $userName = $gifter['username'];
                $chatIntegrationService->sendBroadcast ( sprintf ( "%s is now a %s subscriber! gifted by %s %s", $subscriptionUser['username'], $subscriptionType ['tierLabel'], $gifter['username'], $randomEmote ) );
            }else{
                $userName = $subscriptionUser['username'];
                $chatIntegrationService->sendBroadcast ( sprintf ( "%s is now a %s subscriber! %s", $subscriptionUser['username'], $subscriptionType ['tierLabel'], $randomEmote ) );
            }
            $subMessage = Session::set('subMessage');
            if(!empty($subMessage)){
                $chatIntegrationService->sendBroadcast ( sprintf ( "%s: %s", $userName, $subMessage ) );
            }

            // Redirect to completion page
            return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/complete';
          
        } catch (Exception $e) {

            if (! empty ( $order )){
                $ordersService->updateOrder(array(
                    'orderId' => $order ['orderId'],
                    'state' => OrderStatus::ERROR
                ));
            }
            if (! empty ( $paymentProfile )){
                $ordersService->updatePayment(array(
                    'paymentId' => $paymentProfile ['paymentId'],
                    'paymentStatus' => PaymentStatus::ERROR
                ));
            }
            if (! empty ( $orderSubscription )){
                $subscriptionsService->updateSubscription (array(
                    'subscriptionId' => $orderSubscription ['subscriptionId'],
                    'status' => SubscriptionStatus::ERROR
                ));
            }

            $log->critical ( $e->getMessage(), $order );
            return 'redirect: /subscription/' . urlencode ( $order ['orderId'] ) . '/error';
        }
    }

    /**
     * @Route ("/gift/check")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return Response
     */
    public function giftCheckUser(array $params) {
      FilterParams::required($params, 's');

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
          $data['cangift'] = $subscriptionService->getCanUserReceiveGift ( $userId, $user['userId'] );
          $data['valid']   = true;
      }

      $response = new Response ( Http::STATUS_OK );
      $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
      $response->setBody ( json_encode ( $data ) );
      return $response;
    }
  
}