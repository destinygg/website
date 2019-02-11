<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\ViewModel;
use Destiny\Common\Session\Session;
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
use Destiny\Chat\ChatRedisService;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\FilterParams;
use Destiny\Commerce\PaymentStatus;
use Destiny\Common\Utils\Http;
use Destiny\Google\GoogleRecaptchaHandler;
use Destiny\PayPal\PayPalApiService;
use Destiny\StreamLabs\StreamLabsAlertsType;
use Destiny\StreamLabs\StreamLabsService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class SubscriptionController {

    /**
     * @Route ("/subscribe")
     *
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function subscribe(ViewModel $model) {
        $subscriptionsService = SubscriptionsService::instance();
        if (Session::hasRole(UserRole::USER)) {
            $userId = Session::getCredentials()->getUserId();
            // Active subscription
            $model->subscription = $subscriptionsService->getUserActiveSubscription($userId);
            // Pending subscription
            $model->pending = $subscriptionsService->findByUserIdAndStatus($userId, SubscriptionStatus::PENDING);
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
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionCancel(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');
        $subscriptionsService = SubscriptionsService::instance();
        $userId = Session::getCredentials()->getUserId();
        $subscriptionId = $params['id'];
        $sub = $subscriptionsService->findById($subscriptionId);
        if (empty ($sub) || $sub['userId'] !== $userId || $sub['status'] !== SubscriptionStatus::ACTIVE) {
            throw new Exception ('Must have an active subscription');
        }
        $model->subscription = $sub;
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
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionGiftCancel(array $params, ViewModel $model) {
        FilterParams::required($params, 'id');
        $subscriptionsService = SubscriptionsService::instance();
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        $sub = $subscriptionsService->findById($params['id']);
        if (empty($sub) || $sub['gifter'] !== $userId || $sub['status'] !== SubscriptionStatus::ACTIVE) {
            throw new Exception ('Invalid subscription');
        }
        $model->subscription = $sub;
        $model->giftee = $userService->getUserById($sub['userId']);
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
     *
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionCancelProcess(array $params, ViewModel $model, Request $request) {
        FilterParams::required($params, 'subscriptionId');

        $subscriptionsService = SubscriptionsService::instance();
        $authService = AuthenticationService::instance();
        
        $userId = Session::getCredentials ()->getUserId ();
        $subscription = $subscriptionsService->findById ( $params['subscriptionId'] );

        try {
            $googleRecaptchaHandler = new GoogleRecaptchaHandler();
            $googleRecaptchaHandler->resolveWithRequest($request);

            if (empty($subscription)) {
                throw new Exception('Invalid subscription');
            }
            if ($subscription['userId'] != $userId && $subscription['gifter'] != $userId) {
                throw new Exception('Invalid subscription owner');
            }
            if ($subscription['status'] != SubscriptionStatus::ACTIVE) {
                throw new Exception('Invalid subscription status');
            }
            try {
                $subscriptionsService->cancelSubscription($subscription, isset($params['cancelSubscription']) && $params['cancelSubscription'] == '1');
            } catch (Exception $e) {
                Log::critical("Error cancelling subscription {id}", $subscription);
                throw $e;
            }

            $authService->flagUserForUpdate($subscription ['userId']);
            $model->subscription = $subscription;
            $model->subscriptionCancelled = true;
            $model->title = 'Cancel Subscription';
            return 'profile/cancelsubscription';
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect:/profile';
        }
    }

    /**
     * @Route ("/subscription/confirm")
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     */
    public function subscriptionConfirm(array $params, ViewModel $model) {
        try {
            FilterParams::required($params, 'subscription');

            // If there is no user, save the selection, and go to the login screen
            if (!Session::hasRole(UserRole::USER)) {
                $url = '/subscription/confirm?subscription=' . $params ['subscription'];
                if (isset($params ['gift']) && !empty($params ['gift'])) {
                    $url .= '&gift=' . $params ['gift'];
                }
                return 'redirect: /login?follow=' . urlencode($url);
            }

            $userId = Session::getCredentials()->getUserId();
            $subscriptionsService = SubscriptionsService::instance();
            $subscriptionType = $subscriptionsService->getSubscriptionType($params ['subscription']);
            if (empty($subscriptionType)) {
                throw new Exception("Invalid subscription type");
            }

        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /subscribe';
        }

        // If this is a gift, there is no need to check the current subscription
        if (isset($params['gift']) && !empty($params['gift'])) {
            $model->gift = $params['gift'];
            $model->warning = new Exception('If the giftee has a subscription by the time this payment is completed the subscription will be marked as failed, but your payment will still go through.');
        }
        // Existing subscription
        else {
            $currentSubscription = $subscriptionsService->getUserActiveSubscription($userId);
            if (!empty ($currentSubscription)) {
                $model->currentSubscription = $currentSubscription;
                $model->currentSubscriptionType = $subscriptionsService->getSubscriptionType($currentSubscription ['subscriptionType']);
                // Warn about identical subscription overwrite
                if ($model->currentSubscriptionType['id'] == $subscriptionType ['id']) {
                    $model->warning = new Exception('Already subscribed. Your highest tier subscription will be shown.');
                }
            }
        }

        $model->subscriptionType = $subscriptionType;
        $model->title = 'Subscribe Confirm';
        return 'subscribe/confirm';
    }

    /**
     * @Route ("/subscription/create")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionCreate(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscription');
        
        $userService = UserService::instance ();
        $subscriptionsService = SubscriptionsService::instance ();
        $payPalApiService = PayPalApiService::instance ();
        $userId = Session::getCredentials ()->getUserId ();

        $subscriptionType = $subscriptionsService->getSubscriptionType($params ['subscription']);
        if (empty($subscriptionType)) {
            throw new Exception("Invalid subscription type");
        }

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
                if(!$subscriptionsService->canUserReceiveGift ( $userId, $giftReceiver['userId'] )){
                   throw new Exception ( 'Invalid giftee (user does not accept gifts)' );
                }
            }
        }catch (Exception $e){
            $model->title = 'Subscription Error';
            $model->subscription = null;
            $model->error = $e;
            return 'subscribe/error';
        }

        // Create the NEW subscription
        $start = Date::getDateTime ();
        $end = Date::getDateTime ();
        $end->modify ( '+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower ( $subscriptionType ['billingPeriod'] ) );

        $subscription = [
            'userId'             => $userId,
            'subscriptionSource' => Config::$a ['subscriptionType'],
            'subscriptionType'   => $subscriptionType ['id'],
            'subscriptionTier'   => $subscriptionType ['tier'],
            'createdDate'        => $start->format ( 'Y-m-d H:i:s' ),
            'endDate'            => $end->format ( 'Y-m-d H:i:s' ),
            'recurring'          => ($recurring) ? 1:0,
            'status'             => SubscriptionStatus::_NEW
        ];

        // If this is a gift, change the user and the gifter
        if(!empty($giftReceiver)){
            $subscription['userId'] = $giftReceiver['userId'];
            $subscription['gifter'] = $userId;
        }

        $token = null;
        $conn = Application::getDbConn();
        try {
            $conn->beginTransaction();
            $subscriptionId = $subscriptionsService->addSubscription($subscription);
            $returnUrl = Http::getBaseUrl() . '/subscription/process?success=true&subscriptionId=' . urlencode($subscriptionId);
            $cancelUrl = Http::getBaseUrl() . '/subscription/process?success=false&subscriptionId=' . urlencode($subscriptionId);
            $token = $payPalApiService->createSubscribeECRequest($returnUrl, $cancelUrl, $subscriptionType, $recurring);
            if (empty ($token)) {
                throw new Exception ("Error getting paypal response");
            }
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw new Exception("Error creating order", $e);
        }
        return 'redirect: ' . Config::$a['paypal']['endpoint_checkout'] . urlencode ( $token );
    }

    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     *
     * We were redirected here from PayPal after the buyer approved/cancelled the payment
     *
     * @param array $params
     * @return string
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionProcess(array $params) {
        FilterParams::required($params, 'subscriptionId');
        FilterParams::required($params, 'token');
        FilterParams::declared($params, 'success');

        $userId = Session::getCredentials()->getUserId();
        $chatBanService = ChatBanService::instance();
        $userService = UserService::instance();
        $ordersService = OrdersService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $payPalApiService = PayPalApiService::instance();
        $redisService = ChatRedisService::instance();
        $authService = AuthenticationService::instance();
        $conn = Application::getDbConn();

        $subscription = $subscriptionsService->findById($params ['subscriptionId']);
        if (empty ($subscription) || strcasecmp($subscription ['status'], SubscriptionStatus::_NEW) !== 0) {
            throw new Exception ('Invalid subscription state');
        }
        $subscriptionType = $subscriptionsService->getSubscriptionType($subscription ['subscriptionType']);
        if (empty($subscriptionType)) {
            throw new Exception("Invalid subscription type");
        }

        $user = $userService->getUserById($subscription['userId']);
        if ($user['userId'] != $userId && $subscription['gifter'] != $userId) {
            throw new Exception ('Invalid subscription');
        }

        try {
            if ($params ['success'] == '0' || $params ['success'] == 'false' || $params ['success'] === false) {
                throw new Exception ('Order request failed');
            }

            FilterParams::required($params, 'PayerID'); // if the order status is an error, the payerID is not returned
            Session::remove('subscriptionId');
            Session::remove('token');

            // Create the payment profile
            try {
                if (!$payPalApiService->retrieveCheckoutInfo($params ['token'])) {
                    throw new Exception ('Failed to retrieve express checkout details');
                }
                // Payment date is 1 day before subscription rolls over.
                if ($subscription['recurring'] == 1 || $subscription['recurring'] == true) {
                    $startPaymentDate = Date::getDateTime();
                    $nextPaymentDate = Date::getDateTime();
                    $nextPaymentDate->modify('+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower($subscriptionType ['billingPeriod']));
                    $nextPaymentDate->modify('-1 DAY');
                    $reference = $subscription ['userId'] . '-' . $subscription ['subscriptionId'];
                    $paymentProfileId = $payPalApiService->createSubscriptionPaymentProfile($params ['token'], $reference, $user['username'], $nextPaymentDate, $subscriptionType);
                    if (empty ($paymentProfileId)) {
                        throw new Exception ('Invalid recurring payment profileId returned from Paypal');
                    }
                    $subscription['paymentStatus'] = PaymentStatus::ACTIVE;
                    $subscription['paymentProfileId'] = $paymentProfileId;
                    $subscription['billingStartDate'] = $startPaymentDate->format('Y-m-d H:i:s');
                    $subscription['billingNextDate'] = $nextPaymentDate->format('Y-m-d H:i:s');
                }
                // Record the payments as well as check if any are not in the completed state
                // we put the subscription into "PENDING" state if a payment is found not completed
                $DoECResponse = $payPalApiService->getCheckoutPaymentResponse($params ['PayerID'], $params ['token'], $subscriptionType['amount']);
                $payments = $payPalApiService->getCheckoutResponsePayments($DoECResponse);
            } catch (\Exception $e) {
                $n = new Exception("Error processing paypal checkout response", $e);
                Log::error($n);
                throw $n;
            }
            // Update subscription
            try {
                $conn->beginTransaction();
                if (count($payments) > 0) {
                    $subscription['status'] = SubscriptionStatus::ACTIVE;
                    foreach ($payments as $payment) {
                        $payment['subscriptionId'] = $subscription ['subscriptionId'];
                        $payment['payerId'] = $params ['PayerID'];
                        $ordersService->addPayment($payment);
                    }
                } else {
                    $subscription['status'] = SubscriptionStatus::PENDING;
                }
                $subscriptionsService->updateSubscription([
                    'subscriptionId' => $subscription['subscriptionId'],
                    'paymentStatus' => $subscription['paymentStatus'],
                    'paymentProfileId' => $subscription['paymentProfileId'],
                    'billingStartDate' => $subscription['billingStartDate'],
                    'billingNextDate' => $subscription['billingNextDate'],
                    'status' => $subscription['status']
                ]);
                $conn->commit();
            } catch (DBALException $e) {
                $n = new Exception("Failed to update subscription", $e);
                Log::error($n);
                $conn->rollBack();
                throw $n;
            }
            //
        } catch (Exception $e) {
            Log::critical("Error processing subscription. " . $e->getMessage(), $subscription);
            $subscriptionsService->updateSubscription([
                'subscriptionId' => $subscription ['subscriptionId'],
                'status' => SubscriptionStatus::ERROR
            ]);
            return 'redirect: /subscription/' . urlencode($subscription ['subscriptionId']) . '/error';
        }

        // only unban the user if the ban is non-permanent or the tier of the subscription is >= 2
        // we unban the user if no ban is found because it also unmute's
        try {
            $ban = $chatBanService->getUserActiveBan($user['userId']);
            if (empty ($ban) or (!empty($ban ['endtimestamp']) or $subscriptionType['tier'] >= 2)) {
                $redisService->sendUnban($user['userId']);
            }
        } catch (DBALException $e) {
            $n = new Exception("Could not unban user {userId}", $e);
            Log::error($n, $user);
        }

        // Broadcast
        try {
            $subMessage = Session::getAndRemove('subMessage');
            if (!empty($subscription['gifter'])) {
                $gifter = $userService->getUserById($subscription['gifter']);
                $gifternick = $gifter['username'];
                $message = sprintf("%s gifted %s a %s subscription!", $gifter['username'], $user['username'], $subscriptionType ['tierLabel']);
            } else {
                $gifternick = $user['username'];
                $message = sprintf("%s is now a %s subscriber!", $user['username'], $subscriptionType ['tierLabel']);
            }
            $redisService->sendBroadcast($message);
            if (!empty($subMessage)) {
                $redisService->sendBroadcast("$gifternick said... $subMessage");
            }
            if(Config::$a['streamlabs']['alert_subscriptions']) {
                $streamLabService = StreamLabsService::withAuth();
                $streamLabService->sendAlert([
                    'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
                    'message' => $message
                ]);
            }
        } catch (\Exception $e) {
            $n = new Exception("Error sending subscription broadcast.", $e);
            Log::error($n);
        }
        // Update the user
        try {
            $authService->flagUserForUpdate($user);
        } catch (\Exception $e) {
            Log::error($e);
        }
        // Redirect to completion page
        return 'redirect: /subscription/' . urlencode($subscription ['subscriptionId']) . '/complete';
    }

    /**
     * @Route ("/subscription/{subscriptionId}/complete")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionComplete(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscriptionId');

        $subscriptionsService = SubscriptionsService::instance();
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        $subscription = $subscriptionsService->findById($params ['subscriptionId']);

        if (empty ($subscription) || ($subscription['userId'] != $userId && $subscription['gifter'] != $userId)) {
            throw new Exception ('Invalid subscription record');
        }

        if (!empty($subscription['gifter'])) {
            $giftee = $userService->getUserById($subscription['userId']);
            $model->giftee = $giftee;
        }

        $model->title = 'Subscription Complete';
        $model->subscription = $subscription;
        $model->subscriptionType = $subscriptionsService->getSubscriptionType($subscription ['subscriptionType']);
        return 'subscribe/complete';
    }

    /**
     * @Route ("/subscription/{subscriptionId}/error")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @param ViewModel $model
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionError(array $params, ViewModel $model) {
        FilterParams::required($params, 'subscriptionId');

        $subscriptionsService = SubscriptionsService::instance ();
        $userId = Session::getCredentials ()->getUserId ();

        $subscription = $subscriptionsService->findById ( $params ['subscriptionId'] );
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
     *
     * @throws DBALException
     * @throws Exception
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
            $data['cangift'] = $subscriptionService->canUserReceiveGift($userId, $user['userId']);
            $data['valid'] = true;
        }
        return $data;
    }
  
}