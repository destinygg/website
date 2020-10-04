<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\PaymentStatus;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Application;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Discord\DiscordMessenger;
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
     * @throws Exception
     */
    public function subscribe(ViewModel $model): string {
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
     * @throws Exception
     */
    public function subscriptionCancel(array $params, ViewModel $model): string {
        FilterParams::required($params, 'id');
        $subscriptionsService = SubscriptionsService::instance();
        $userId = Session::getCredentials()->getUserId();
        $subscriptionId = $params['id'];
        $sub = $subscriptionsService->findById($subscriptionId);
        if (empty ($sub) || $sub['userId'] !== $userId ) {
            Session::setErrorBag('Invalid subscription');
            return 'redirect: /profile';
        }
        $model->subscription = $sub;
        $model->title = 'Cancel Subscription';
        return 'profile/cancelsubscription';
    }
    
    /**
     * @Route ("/subscription/gift/{id}/cancel")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @throws Exception
     */
    public function subscriptionGiftCancel(array $params, ViewModel $model): string {
        FilterParams::required($params, 'id');
        $subscriptionsService = SubscriptionsService::instance();
        $userService = UserService::instance();
        $userId = Session::getCredentials()->getUserId();
        $sub = $subscriptionsService->findById($params['id']);
        if (empty($sub) || $sub['status'] !== SubscriptionStatus::ACTIVE) {
            Session::setErrorBag('Must be an valid subscription');
            return 'redirect: /profile';
        }
        if ($sub['gifter'] !== $userId) {
            Session::setErrorBag('Not allowed to cancel this subscription');
            return 'redirect: /profile';
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
     * @throws Exception
     */
    public function subscriptionCancelProcess(array $params, Request $request): string {
        FilterParams::required($params, 'subscriptionId');

        $subService = SubscriptionsService::instance();
        $authService = AuthenticationService::instance();

        $creds = Session::getCredentials();
        $userId = $creds->getUserId();
        $subscription = $subService->findById($params['subscriptionId']);

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
            if ($subscription['recurring'] == 1) {
                $subscription = $subService->cancelSubscription($subscription, false, $userId);
                Session::setSuccessBag('Subscription payment stopped. You can now remove the subscription.');
            } else {
                $subscription = $subService->cancelSubscription($subscription, true, $userId);
                Session::setSuccessBag('Subscription removed.');
            }

            $note = $params['message'] ?? '';
            if (!empty($message)) {
                DiscordMessenger::send('Subscription cancelled', [
                    'fields' => [
                        ['title' => 'User', 'value' => DiscordMessenger::userLink($creds->getUserId(), $creds->getUsername()), 'short' => false],
                        ['title' => 'Message', 'value' => $note, 'short' => false],
                    ]
                ]);
            }

            $authService->flagUserForUpdate($subscription ['userId']);
            return "redirect: /subscription/${subscription['subscriptionId']}/cancel";
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect:/profile';
        }
    }

    /**
     * @Route ("/subscription/confirm")
     * @throws Exception
     */
    public function subscriptionConfirm(array $params, ViewModel $model): string {
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
        }
        // Existing subscription check
        else {
            $currentSubscription = $subscriptionsService->getUserActiveSubscription($userId);
            if (!empty ($currentSubscription)) {
                $model->currentSubscription = $currentSubscription;
                $model->currentSubscriptionType = $subscriptionsService->getSubscriptionType($currentSubscription ['subscriptionType']);
                // Warn about identical subscription overwrite
                if ($model->currentSubscriptionType['id'] == $subscriptionType ['id']) {
                    // Too verbose?
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
     * @throws Exception
     * @throws DBALException
     */
    public function subscriptionCreate(array $params, ViewModel $model): string {
        FilterParams::required($params, 'subscription');

        $userService = UserService::instance();
        $subService = SubscriptionsService::instance();
        $payPalApiService = PayPalApiService::instance();
        $creds = Session::getCredentials();
        $userId = $creds->getUserId();

        $subscriptionType = $subService->getSubscriptionType($params ['subscription']);
        if (empty($subscriptionType)) {
            throw new Exception("Invalid subscription type");
        }

        $recurring = (isset ($params ['renew']) && $params ['renew'] == '1');
        $giftReceiverUsername = (isset($params['gift']) && !empty($params['gift'])) ? $params['gift'] : null;
        $giftReceiver = null;

        try {
            if (!empty($giftReceiverUsername)) {
                $giftReceiver = $userService->getUserByUsername($giftReceiverUsername);
                if (empty($giftReceiver)) {
                    throw new Exception ('Invalid giftee (user not found)');
                }
                if ($userId == $giftReceiver['userId']) {
                    throw new Exception ('Invalid giftee (cannot gift yourself)');
                }
                if (!$subService->canUserReceiveGift($userId, $giftReceiver['userId'])) {
                    throw new Exception ('Invalid giftee (user does not accept gifts)');
                }
            }
        } catch (Exception $e) {
            $model->title = 'Subscription Error';
            $model->subscription = null;
            $model->error = $e;
            return 'subscribe/error';
        }

        // Create the NEW subscription
        $start = Date::getDateTime();
        $end = Date::getDateTime();
        $end->modify('+' . $subscriptionType ['billingFrequency'] . ' ' . strtolower($subscriptionType ['billingPeriod']));

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


        // Send a message to discord containing the sub note
        // We are not store this value
        $note = $params['sub-note'] ?? '';
        if (!empty($note)) {
            Session::set('subscribeMessage', $note);
        }

        // We set a session variable for the broadcastMessage
        // Since this is not stored on the subscription itself, and we only want
        // to action the message on SUCCESSFUL authentication
        $message = $params['sub-message'] ?? '';
        if (!empty($message)) {
            Session::set('broadcastMessage', $message);
        }

        // If this is a gift, change the user and the gifter
        if (!empty($giftReceiver)) {
            $subscription['userId'] = $giftReceiver['userId'];
            $subscription['gifter'] = $userId;
        }

        $conn = Application::getDbConn();
        try {
            $conn->beginTransaction();
            $subscriptionId = $subService->addSubscription($subscription);
            $returnUrl = Http::getBaseUrl() . '/subscription/process?success=true&subscriptionId=' . urlencode($subscriptionId);
            $cancelUrl = Http::getBaseUrl() . '/subscription/process?success=false&subscriptionId=' . urlencode($subscriptionId);
            $token = $payPalApiService->createSubscribeECRequest($returnUrl, $cancelUrl, $subscriptionType, $recurring);
            $conn->commit();
            return 'redirect: ' . Config::$a['paypal']['endpoint_checkout'] . urlencode($token);
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Error creating order", $e);
        }
    }

    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     *
     * We were redirected here from PayPal after the buyer approved/cancelled the payment
     * TODO this method is massive
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionProcess(array $params): string {
        FilterParams::required($params, 'subscriptionId');
        FilterParams::required($params, 'token');
        FilterParams::declared($params, 'success');

        $subscribeMessage = Session::getAndRemove('subscribeMessage');
        $broadcastMessage = Session::getAndRemove('broadcastMessage');

        $creds = Session::getCredentials();
        $userId = $creds->getUserId();
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
                $checkoutDetails = $payPalApiService->retrieveCheckoutInfo($params['token']);
                $doECResponse = $payPalApiService->completeSubscribeECTransaction($checkoutDetails);
                $payments = $payPalApiService->getCheckoutResponsePayments($doECResponse);
            } catch (Exception $e) {
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
                        $payment['payerId'] = $params ['PayerID'];
                        $paymentId = $ordersService->addPayment($payment);
                        $ordersService->addPurchaseOfSubscription($paymentId, $subscription['subscriptionId']);
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

        try {
            $ban = $chatBanService->getUserActiveBan($user['userId']);
            if (empty($ban) || !$chatBanService->isPermanentBan($ban)) {
                $redisService->sendUnbanAndUnmute($user['userId']);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        // Broadcast

        // Broadcast the subscription
        if (!empty($subscription['gifter'])) {
            $gifter = $userService->getUserById($subscription['gifter']);
            $gifternick = $gifter['username'];
            $message = sprintf("%s gifted %s a %s subscription!", $gifter['username'], $user['username'], $subscriptionType ['tierLabel']);
        } else {
            $gifternick = $user['username'];
            $message = sprintf("%s is now a %s subscriber!", $user['username'], $subscriptionType ['tierLabel']);
        }
        $redisService->sendBroadcast($message);

        // Broadcast message
        if (!empty($broadcastMessage) && !empty(trim($broadcastMessage))) {
            $message = mb_substr($broadcastMessage, 0, 250);
            $redisService->sendBroadcast("$gifternick said... $message");
            if (Config::$a[AuthProvider::STREAMLABS]['alert_subscriptions']) {
                StreamLabsService::instance()->sendAlert([
                    'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
                    'message' => $message
                ]);
            }
        }

        // Sub message
        if (!empty(trim($subscribeMessage ?? ''))) {
            DiscordMessenger::send('New subscriber', [
                'fields' => [
                    ['title' => 'User', 'value' => DiscordMessenger::userLink($creds->getUserId(), $creds->getUsername()), 'short' => false],
                    ['title' => 'Message', 'value' => mb_substr($broadcastMessage ?? 'No message', 0, 250), 'short' => false],
                ]
            ]);
        }

        // Update the user
        $authService->flagUserForUpdate($user['userId']);

        // We pass the token rather than the transaction ID to handle scenarios
        // where the payment is still pending and there is no transaction ID. A
        // token expires after three hours.
        return "redirect: /subscription/complete?token=" . $params['token'];
    }

    /**
     * @Route ("/subscription/complete")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function subscriptionComplete(array $params, ViewModel $model): string {
        FilterParams::required($params, 'token');

        $checkoutDetails = PayPalApiService::instance()->retrieveCheckoutInfo($params['token']);
        $paymentDetails = $checkoutDetails->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0];
        $subscriptionType = SubscriptionsService::instance()->getSubscriptionType(
            $paymentDetails->PaymentDetailsItem[0]->Number
        );

        $model->title = 'Subscription Complete';
        $model->quantity = $paymentDetails->PaymentDetailsItem[0]->Quantity;
        // There is no `TransactionId` if the transaction is pending.
        $model->transactionId = $paymentDetails->TransactionId ?? null;
        $model->orderTotal = $paymentDetails->OrderTotal->value;
        $model->subscriptionType = $subscriptionType;
        return 'subscribe/complete';
    }

    /**
     * @Route ("/subscription/{subscriptionId}/error")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function subscriptionError(array $params, ViewModel $model): string {
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
     * @throws Exception
     */
    public function giftCheckUser(array $params): array {
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