<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatEmotes;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Commerce\PaymentStatus;
use Destiny\Common\Utils\Http;
use Destiny\Google\GoogleRecaptchaHandler;
use Destiny\PayPal\PayPalApiService;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;

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
            $subscription = $subscriptionsService->getSubscriptionByUserIdAndStatus ( $userId, SubscriptionStatus::PENDING );
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
     * @Route ("/subscription/{id}/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $model
     * @throws Exception
     * @return string
     */
    public function subscriptionCancel(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');

        $subscriptionsService = SubscriptionsService::instance ();
        $userId = Session::getCredentials ()->getUserId ();
        $subscriptionId = $params['id'];

        $subscription = $subscriptionsService->getSubscriptionByIdAndUserIdAndStatus ( $subscriptionId, $userId, SubscriptionStatus::ACTIVE );
        if (empty ( $subscription )) {
            throw new Exception ( 'Must have an active subscription' );
        }

        $model->subscription = $subscription;
        $model->title = 'Cancel Subscription';
        return 'profile/cancelsubscription';
    }
    
    /**
     * @Route ("/subscription/gift/{id}/cancel")
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
        $subscription = $subscriptionsService->getSubscriptionByIdAndGifterIdAndStatus ( $params['id'], $userId, SubscriptionStatus::ACTIVE );
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
     * @param Request $request
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function subscriptionCancelProcess(array $params, ViewModel $model, Request $request) {
        FilterParams::required($params, 'subscriptionId');

        $payPalAPIService = PayPalApiService::instance ();
        $subscriptionsService = SubscriptionsService::instance();
        $authenticationService = AuthenticationService::instance();
        
        $userId = Session::getCredentials ()->getUserId ();
        $subscription = $subscriptionsService->getSubscriptionById ( $params['subscriptionId'] );

        $googleRecaptchaHandler = new GoogleRecaptchaHandler();
        $googleRecaptchaHandler->resolve($params['g-recaptcha-response'], $request);

        if(empty($subscription)){
           throw new Exception( 'Invalid subscription' );
        }

        if($subscription['userId'] != $userId && $subscription['gifter'] != $userId){
            throw new Exception( 'Invalid subscription owner' );
        }

        if($subscription['status'] != SubscriptionStatus::ACTIVE){
           throw new Exception( 'Invalid subscription status' );
        }

        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {

            // Cancel the payment profile
            if (! empty ( $subscription ['paymentProfileId'] )) {
                if (strcasecmp ( $subscription ['paymentStatus'], PaymentStatus::ACTIVE ) === 0) {
                    $payPalAPIService->cancelPaymentProfile ( $subscription ['paymentProfileId'] );
                    $subscription['paymentStatus'] = PaymentStatus::CANCELLED;
                }
                $subscription['recurring'] = 0;
            }

            // Cancel subscription
            if(isset($params['cancelSubscription']) && $params['cancelSubscription'] == '1'){
                $subscription['status'] = SubscriptionStatus::CANCELLED;
                $subscriptionsService->updateSubscription (array(
                    'subscriptionId' => $subscription ['subscriptionId'],
                    'status' => $subscription['status']
                ));
            }

            // Update the subscription info
            $subscriptionsService->updateSubscription(array(
                'subscriptionId' => $subscription['subscriptionId'],
                'paymentStatus'  => $subscription['paymentStatus'],
                'recurring'      => $subscription['recurring'],
                'status'         => $subscription['status']
            ));

            $authenticationService->flagUserForUpdate ( $subscription ['userId'] );
            $conn->commit();
        } catch ( \Exception $e ) {
            Log::critical("Error cancelling subscription", $subscription);
            $conn->rollBack();
            throw $e;
        }
        
        $model->subscription = $subscription;
        $model->subscriptionCancelled = true;
        $model->title = 'Cancel Subscription';
        return 'profile/cancelsubscription';
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
        return 'subscribe/confirm';
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
        $payPalApiService = PayPalApiService::instance ();

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
            $model->title = 'Subscription Error';
            $model->subscription = null;
            $model->error = $e;
            return 'subscribe/error';
        }

        $conn = Application::instance()->getConnection();
        $conn->beginTransaction();

        try {

            // Create the NEW subscription
            $start = Date::getDateTime ();
            $end = Date::getDateTime ();
            $end->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );

            $subscription = array (
                'userId'             => $userId,
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
            $subscriptionId = $subscriptionsService->addSubscription ( $subscription );

            // Send request to paypal
            $returnUrl = Http::getBaseUrl () . '/subscription/process?success=true&subscriptionId=' . urlencode ( $subscriptionId );
            $cancelUrl = Http::getBaseUrl () . '/subscription/process?success=false&subscriptionId=' . urlencode ( $subscriptionId );

            $token = $payPalApiService->createSubscribeECResponse($returnUrl, $cancelUrl, $subscriptionType, $recurring);
            if (empty ($token)) {
                throw new Exception ("Error getting paypal response");
            }

            // Commit transaction and continue to paypal.
            $conn->commit();

            return 'redirect: ' . Config::$a ['paypal'] ['api'] ['endpoint'] . urlencode ( $token );

        } catch ( \Exception $e ) {
            Log::critical("Error creating order");
            $conn->rollBack();
            throw $e;
        }
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

        FilterParams::required ( $params, 'subscriptionId' );
        FilterParams::required ( $params, 'token' );
        FilterParams::declared ( $params, 'success' );

        $userId = Session::getCredentials ()->getUserId ();
        $userService = UserService::instance ();
        $ordersService = OrdersService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        $chatIntegrationService = ChatIntegrationService::instance ();
        $authenticationService = AuthenticationService::instance ();

        $subscription = $subscriptionsService->getSubscriptionById ( $params ['subscriptionId'] );
        if (empty ( $subscription ) || strcasecmp($subscription ['status'], SubscriptionStatus::_NEW) !== 0)
            throw new Exception ( 'Invalid subscription record' );

        try {

            $subscriptionType = $subscriptionsService->getSubscriptionType($subscription ['subscriptionType']);
            $user = $userService->getUserById( $subscription['userId'] );

            if ($user['userId'] != $userId && $subscription['gifter'] != $userId)
                throw new Exception ('Invalid subscription');

            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false)
                throw new Exception ('Order request failed');

            $checkinfo = $payPalApiService->retrieveCheckoutInfo($params ['token']);
            if ($checkinfo === null)
                throw new Exception ('Failed to retrieve express checkout details');

            FilterParams::required($params, 'PayerID'); // if the order status is an error, the payerID is not returned
            Session::set('subscriptionId');
            Session::set('token');

            // Create the payment profile
            // Payment date is 1 day before subscription rolls over.
            if ($subscription['recurring'] == 1 || $subscription['recurring'] == true) {
                $startPaymentDate = Date::getDateTime();
                $nextPaymentDate = Date::getDateTime();
                $nextPaymentDate->modify('+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower($subscriptionType ['billingPeriod']));
                $nextPaymentDate->modify('-1 DAY');

                $reference = $subscription ['userId'] . '-' . $subscription ['subscriptionId'];
                $paymentProfileId = $payPalApiService->createRecurringPaymentProfile($params ['token'], $reference, $user['username'], $nextPaymentDate, $subscriptionType);
                if (empty ($paymentProfileId))
                    throw new Exception ('Invalid recurring payment profileId returned from Paypal');

                $subscriptionsService->updateSubscription(array(
                    'subscriptionId' => $subscription ['subscriptionId'],
                    'paymentStatus' => PaymentStatus::ACTIVE,
                    'paymentProfileId' => $paymentProfileId,
                    'billingStartDate' => $startPaymentDate->format('Y-m-d H:i:s'),
                    'billingNextDate' => $nextPaymentDate->format('Y-m-d H:i:s')
                ));
            }

            // Record the payments as well as check if any are not in the completed state
            // we put the subscription into "PENDING" state if a payment is found not completed
            $subscriptionStatus = SubscriptionStatus::ACTIVE;
            $DoECResponse = $payPalApiService->getECPaymentResponse($params ['PayerID'], $params ['token'], $subscriptionType['amount']);
            $payments = $payPalApiService->getResponsePayments($DoECResponse);
            foreach ($payments as $payment) {
                $payment ['subscriptionId'] = $subscription ['subscriptionId'];
                $payment ['payerId'] = $params ['PayerID'];
                $ordersService->addPayment($payment);
                // TODO: Payment provides no way of telling if the transaction with ALL payments was successful
                if ($payment['paymentStatus'] != PaymentStatus::COMPLETED) {
                    $subscriptionStatus = SubscriptionStatus::PENDING;
                }
            }

            // Update subscription status
            $subscriptionsService->updateSubscription(array(
                'subscriptionId' => $subscription ['subscriptionId'],
                'status' => $subscriptionStatus
            ));

        } catch (Exception $e) {

            $subscriptionsService->updateSubscription (array(
                'subscriptionId' => $subscription ['subscriptionId'],
                'status' => SubscriptionStatus::ERROR
            ));

            Log::critical ( $e->getMessage(), $subscription );
            return 'redirect: /subscription/' . urlencode ( $subscription ['subscriptionId'] ) . '/error';
        }

        // only unban the user if the ban is non-permanent or the tier of the subscription is >= 2
        // we unban the user if no ban is found because it also unmutes
        $ban = $userService->getUserActiveBan ( $user['userId'] );
        if (empty ( $ban ) or ( !empty( $ban ['endtimestamp'] ) or $subscriptionType['tier'] >= 2 ) ) {
           $chatIntegrationService->sendUnban ( $user['userId'] );
        }

        // Broadcast
        try {
            $subMessage = Session::set('subMessage');
            $randomEmote = ChatEmotes::random('destiny');
            if (!empty($subscription['gifter'])) {
                $gifter = $userService->getUserById($subscription['gifter']);
                $gifternick = $gifter['username'];
                $message = sprintf("%s gifted %s a %s subscription!", $gifter['username'], $user['username'], $subscriptionType ['tierLabel']);
            } else {
                $gifternick = $user['username'];
                $message = sprintf("%s is now a %s subscriber!", $user['username'], $subscriptionType ['tierLabel']);
            }
            $broadcast = $message . ' ' . $randomEmote;
            if (!empty($subMessage)) {
                $subMessage = trim(preg_replace('/\s\s+/', ' ', $subMessage));
                $broadcast .= "\r" . $gifternick . " said... \r" . $subMessage;
            }
            $chatIntegrationService->sendBroadcast($broadcast);
            $streamLabService = StreamLabsService::instance();
            $streamLabService->useDefaultAuth();
            $streamLabService->sendAlert(['message' => $message, 'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION]);
        } catch (\Exception $e) {
            Log::critical("Error sending subscription broadcast");
        }

        // Update the user
        $authenticationService->flagUserForUpdate ( $user['userId'] );

        // Redirect to completion page
        return 'redirect: /subscription/' . urlencode ( $subscription ['subscriptionId'] ) . '/complete';
    }

    /**
     * @Route ("/subscription/{subscriptionId}/complete")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionComplete(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscriptionId');

        $subscriptionsService = SubscriptionsService::instance ();
        $userService = UserService::instance ();
        $userId = Session::getCredentials ()->getUserId ();
        $subscription = $subscriptionsService->getSubscriptionById ( $params ['subscriptionId'] );

        if( empty ( $subscription ) || ($subscription['userId'] != $userId && $subscription['gifter'] != $userId) )
            throw new Exception ( 'Invalid subscription record' );

        $subscriptionType = $subscriptionsService->getSubscriptionType($subscription ['subscriptionType']);

        if(!empty($subscription['gifter'])){
            $giftee = $userService->getUserById ( $subscription['userId'] );
            $model->giftee = $giftee;
        }

        $model->title = 'Subscription Complete';
        $model->subscription = $subscription;
        $model->subscriptionType = $subscriptionType;
        return 'subscribe/complete';
    }

    /**
     * @Route ("/subscription/{subscriptionId}/error")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     * @throws Exception
     * @throws \Destiny\Common\Utils\FilterParamsException
     */
    public function subscriptionError(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscriptionId');

        $subscriptionsService = SubscriptionsService::instance ();
        $userId = Session::getCredentials ()->getUserId ();

        $subscription = $subscriptionsService->getSubscriptionById ( $params ['subscriptionId'] );
        if( empty ( $subscription ) || ($subscription['userId'] != $userId && $subscription['gifter'] != $userId) )
            throw new Exception ( 'Invalid subscription record' );

        $model->title = 'Subscription Error';
        $model->subscription = $subscription;
        return 'subscribe/error';
    }

    /**
     * @Route ("/api/info/giftcheck")
     * @Secure ({"USER"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     */
    public function giftCheckUser(array $params) {
        FilterParams::required($params, 's');
        $userService = UserService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $userId = Session::getCredentials()->getUserId();
        $data = [
            'valid' => false,
            'cangift' => false,
            'username' => $params ['s']
        ];
        $user = $userService->getUserByUsername($params ['s']);
        if (!empty($user)) {
            $data['cangift'] = $subscriptionService->getCanUserReceiveGift($userId, $user['userId']);
            $data['valid'] = true;
        }
        return $data;
    }
  
}