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
        $model->title = 'Subscribe';
        $model->tiers = Config::$a['commerce']['tiers'];
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
            FilterParams::required($params, 'quantity');

            $isDirectGift = !empty($params['gift']);
            $isMassGift = $params['quantity'] > 1;

            // If the user isn't logged in, save their selection and redirect to
            // the login screen. After logging in, they're redirected back to
            // this page.
            if (!Session::hasRole(UserRole::USER)) {
                $confirmUrl = '/subscription/confirm' . '?' . http_build_query([
                    'subscription' => $params['subscription'],
                    'quantity' => $params['quantity'],
                    'gift' => $params['gift'] ?? null
                ]);

                $loginUrl = '/login' . '?' . http_build_query([
                    'follow' => $confirmUrl
                ]);

                return "redirect: $loginUrl";
            }

            $userId = Session::getCredentials()->getUserId();

            // Validate the request.
            $subscriptionsService = SubscriptionsService::instance();
            $subscriptionType = $subscriptionsService->getSubscriptionType($params['subscription']);
            if (empty($subscriptionType)) {
                throw new Exception('Invalid subscription type.');
            } else if ($isDirectGift && $isMassGift) {
                throw new Exception('A sub cannot be a direct gift and mass gift at once.');
            } else if ($params['quantity'] > 100 || $params['quantity'] < 1) {
                throw new Exception('You can only mass gift between 1 and 100 subs.');
            } else if ($isDirectGift) {
                $giftReceiver = UserService::instance()->getUserByUsername($params['gift']);
                if (empty($giftReceiver)) {
                    throw new Exception('Invalid giftee: no such user exists.');
                } else if ($giftReceiver['userId'] === $userId) {
                    throw new Exception('Invalid giftee: you cannot gift yourself a sub.');
                } else if (!$subscriptionsService->canUserReceiveGift($userId, $giftReceiver['userId'])) {
                    throw new Exception('Invalid giftee: this user can\'t accept gift subs.');
                }
            }
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /subscribe';
        }

        // If this isn't a direct gift or a mass gift, we need to check the
        // user's current subscription and warn them if they're already
        // subscribed.
        if (!$isDirectGift && !$isMassGift) {
            $currentSubscription = $subscriptionsService->getUserActiveSubscription($userId);
            if (!empty($currentSubscription)) {
                $currentSubType = $subscriptionsService->getSubscriptionType($currentSubscription['subscriptionType']);
                $warningMessage = "You already have a {$currentSubType['tierLabel']} subscription! You can sub again, but only your highest tier sub will be visible.";
                $model->warning = new Exception($warningMessage);
            }
        }

        $model->subscriptionType = $subscriptionType;
        $model->quantity = $params['quantity'];
        $model->gift = $params['gift'] ?? null;
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
            return 'subscribe/error';
        }

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

        try {
            $returnUrl = Http::getBaseUrl() . '/subscription/process?' . http_build_query([
                'subTypeId' => $params['subscription'],
                'giftee' => !empty($giftReceiver) ? $giftReceiver['userId'] : null,
                'recurring' => $recurring
            ]);
            $cancelUrl = Http::getBaseUrl() . '/subscribe';

            $token = $payPalApiService->createSubscribeECRequest($returnUrl, $cancelUrl, $subscriptionType, $recurring);
            return 'redirect: ' . Config::$a['paypal']['endpoint_checkout'] . urlencode($token);
        } catch (Exception $e) {
            throw new Exception("Error creating order", $e);
        }
    }

    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     *
     * We were redirected here from PayPal after the buyer approved the payment
     * TODO this method is massive
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionProcess(array $params): string {
        FilterParams::required($params, 'subTypeId');
        FilterParams::required($params, 'token');

        $userService = UserService::instance();
        $subscriptionsService = SubscriptionsService::instance();
        $payPalApiService = PayPalApiService::instance();
        $redisService = ChatRedisService::instance();
        $ordersService = OrdersService::instance();
        $db = Application::getDbConn();

        $subscriptionType = $subscriptionsService->getSubscriptionType($params['subTypeId']);
        if (empty($subscriptionType)) {
            throw new Exception('Invalid subscription type.');
        }

        // The logged in user is the one buying the sub.
        $userId = Session::getCredentials()->getUserId();
        $buyingUser = $userService->getUserById($userId);

        // If there is no giftee, the recipient is the buyer.
        if (!empty($params['giftee'])) {
            $receivingUser = $userService->getUserByUsername($params['giftee']);
        } else {
            $receivingUser = $buyingUser;
        }

        try {
            $db->beginTransaction();

            // No `PayerId` is provided if there was an issue setting up
            // payment.
            FilterParams::required($params, 'PayerID');

            // Create a new subscription.
            $startDate = Date::getDateTime();
            $endDate = Date::getDateTime();
            $endDate->modify("+{$subscriptionDetails['billingFrequency']} {$subscriptionDetails['billingPeriod']}");

            $subscription = [
                'userId'             => $receivingUser['userId'],
                'gifter'             => $receivingUser['userId'] !== $buyingUser['userId'] ? $buyingUser['userId'] : null,
                'subscriptionSource' => Config::$a['subscriptionType'],
                'subscriptionType'   => $subscriptionType['id'],
                'subscriptionTier'   => $subscriptionType['tier'],
                'createdDate'        => $startDate->format('Y-m-d H:i:s'),
                'endDate'            => $endDate->format('Y-m-d H:i:s'),
                'recurring'          => intval($params['recurring'] ?? false),
                'status'             => SubscriptionStatus::_NEW
            ];
            $subscription['subscriptionId'] = $subscriptionsService->addSubscription($subscription);

            // Create a recurring payment profile for recurring subs.
            if (boolval($params['recurring'] ?? 0)) {
                $startPaymentDate = $startDate;
                // The next payment date is one day before the sub expires.
                $nextPaymentDate = (clone $endDate)->modify('-1 day');
                $reference = "{$receivingUser['userId']}-{$subscription['subscriptionId']}";

                $paymentProfileId = $payPalApiService->createSubscriptionPaymentProfile(
                    $params['token'],
                    $reference,
                    $receivingUser['username'],
                    $nextPaymentDate,
                    $subscriptionType
                );
                if (empty($paymentProfileId)) {
                    throw new Exception('Invalid recurring payment profile ID returned from PayPal.');
                }

                $subscription['paymentStatus'] = PaymentStatus::ACTIVE;
                $subscription['paymentProfileId'] = $paymentProfileId;
                $subscription['billingStartDate'] = $startPaymentDate->format('Y-m-d H:i:s');
                $subscription['billingNextDate'] = $nextPaymentDate->format('Y-m-d H:i:s');
            }

            // Retrieve checkout info created in `/subscription/create` and
            // complete the transaction.
            $checkoutDetails = $payPalApiService->retrieveCheckoutInfo($params['token']);
            $doECResponse = $payPalApiService->completeSubscribeECTransaction($checkoutDetails);
            $payments = $payPalApiService->getCheckoutResponsePayments($doECResponse);

            // If there are no payments, assume the transaction is pending. We
            // mark the sub as pending until we receive an IPN from PayPal.
            if (count($payments) > 0) {
                $subscription['status'] = SubscriptionStatus::ACTIVE;
                foreach ($payments as $payment) {
                    $payment['payerId'] = $params['PayerID'];
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
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Log::critical("Error processing subscription. {$e}");
            return 'redirect: /subscription/error';
        }

        // Unban/unmute the newly-subscribed user.
        try {
            $chatBanService = ChatBanService::instance();
            $ban = $chatBanService->getUserActiveBan($receivingUser['userId']);
            if (empty($ban) || !$chatBanService->isPermanentBan($ban)) {
                $redisService->sendUnbanAndUnmute($receivingUser['userId']);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        // Broadcast the subscription in chat.
        if ($receivingUser['userId'] !== $buyingUser['userId']) {
            $message = "{$buyingUser['username']} gifted {$receivingUser['username']} a {$subscriptionDetails['tierLabel']} subscription!";
        } else {
            $message = "{$receivingUser['username']} is now a {$subscriptionDetails['tierLabel']} subscriber!";
        }
        $redisService->sendBroadcast($message);

        // Display an alert on stream and in chat.
        $broadcastMessage = Session::getAndRemove('broadcastMessage');
        $broadcastMessage = mb_substr(trim($broadcastMessage), 0, 250);
        if ($broadcastMessage !== '') {
            $redisService->sendBroadcast("{$buyingUser['username']} said... $broadcastMessage");
            if (Config::$a[AuthProvider::STREAMLABS]['alert_subscriptions']) {
                StreamLabsService::instance()->sendAlert([
                    'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
                    'message' => $broadcastMessage
                ]);
            }
        }

        // Log the subscription event in Discord.
        $subscribeMessage = Session::getAndRemove('subscribeMessage');
        $subscribeMessage = mb_substr(trim($subscribeMessage), 0, 250);
        if ($subscribeMessage !== '') {
            DiscordMessenger::send('New subscriber', [
                'fields' => [
                    ['title' => 'User', 'value' => DiscordMessenger::userLink($buyingUser['userId'], $buyingUser['username']), 'short' => false],
                    ['title' => 'Message', 'value' => $subscribeMessage, 'short' => false],
                ]
            ]);
        }

        AuthenticationService::instance()->flagUserForUpdate($receivingUser['userId']);

        // We pass the token rather than the transaction ID to handle scenarios
        // where the payment is still pending and there is no transaction ID. A
        // token expires after three hours.
        return "redirect: /subscription/complete?token={$params['token']}";
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
     * @Route ("/subscription/error")
     * @Secure ({"USER"})
     * @throws Exception
     */
    public function subscriptionError(array $params, ViewModel $model): string {
        $model->title = 'Subscription Error';
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