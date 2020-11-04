<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\PaymentStatus;
use Destiny\Commerce\SubPurchaseType;
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
use Destiny\Common\User\UserFeature;
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
            FilterParams::required($params, 'subscriptionId');
            FilterParams::required($params, 'purchaseType');
            FilterParams::required($params, 'quantity');

            // If the user isn't logged in, save their selection and redirect to
            // the login screen. After logging in, they're redirected back to
            // this page.
            if (!Session::hasRole(UserRole::USER)) {
                $confirmUrl = '/subscription/confirm' . '?' . http_build_query([
                    'subscriptionId' => $params['subscriptionId'],
                    'purchaseType' => $params['purchaseType'],
                    'quantity' => $params['quantity'],
                    'giftee' => $params['giftee'] ?? null
                ]);

                $loginUrl = '/login' . '?' . http_build_query([
                    'follow' => $confirmUrl
                ]);

                return "redirect: $loginUrl";
            }

            $this->validateSubscriptionParameters($params);
        } catch (Exception $e) {
            Session::setErrorBag($e->getMessage());
            return 'redirect: /subscribe';
        }

        $subscriptionsService = SubscriptionsService::instance();
        $subscriptionType = $subscriptionsService->getSubscriptionType($params['subscriptionId']);

        // If this isn't a direct gift or a mass gift, we need to warn the user
        // if they already have an active or pending subscription.
        if ($params['purchaseType'] === SubPurchaseType::_SELF) {
            $userId = Session::getCredentials()->getUserId();
            $currentSubscriptions = $subscriptionsService->getUserActiveAndPendingSubscriptions($userId);

            if (!empty($currentSubscriptions)) {
                $currentSubscription = $currentSubscriptions[0];
                $currentSubType = $subscriptionsService->getSubscriptionType($currentSubscription['subscriptionType']);

                if ($currentSubscription['status'] === SubscriptionStatus::ACTIVE) {
                    $warningMessage = "You already have a {$currentSubType['tierLabel']} subscription! You can sub again, but only your highest tier sub will be visible.";    
                } else { // SubscriptionStatus::PENDING
                    $warningMessage = "You already have a pending {$currentSubType['tierLabel']} subscription! Pending subs become active when their payment is processed.";
                }

                $model->warning = new Exception($warningMessage);
            }
        }

        $model->subscriptionType = $subscriptionType;
        $model->purchaseType = $params['purchaseType'];
        $model->quantity = $params['quantity'];
        $model->giftee = $params['giftee'] ?? null;
        $model->title = 'Subscribe Confirm';
        return 'subscribe/confirm';
    }

    /**
     * @Route ("/subscription/create")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     * @throws Exception
     * @throws DBALException
     */
    public function subscriptionCreate(array $params, ViewModel $model): string {
        try {
            FilterParams::required($params, 'subscriptionId');
            FilterParams::required($params, 'purchaseType');
            FilterParams::required($params, 'quantity');

            $this->validateSubscriptionParameters($params);
        } catch (Exception $e) {
            $model->title = 'Subscription Error';
            return 'subscribe/error';
        }

        // How the user heard of the streamer or why they're subscribing. We
        // pass this and the broadcast message to `/subscribe/complete` via the
        // user's session.
        if (!empty($params['sub-note'])) {
            Session::set('subscribeMessage', $params['sub-note']);
        }

        if (!empty($params['sub-message'])) {
            Session::set('broadcastMessage', $params['sub-message']);
        }

        try {
            $subscriptionType = SubscriptionsService::instance()->getSubscriptionType($params['subscriptionId']);
            $recurring = !empty($params['recurring']) ? $params['recurring'] === '1' : false;
            $returnUrl = Http::getBaseUrl() . '/subscription/process';
            $cancelUrl = Http::getBaseUrl() . '/subscribe';

            $token = PayPalApiService::instance()->createSubscribeECRequest(
                $returnUrl,
                $cancelUrl,
                $params['purchaseType'],
                $subscriptionType,
                $recurring,
                $params['quantity'],
                $params['giftee'] ?? null
            );
            return 'redirect: ' . Config::$a['paypal']['endpoint_checkout'] . urlencode($token);
        } catch (Exception $e) {
            Log::critical("Error creating order. {$e}");
            return 'redirect: /subscription/error';
        }
    }

    /**
     * @Route ("/subscription/process")
     * @Secure ({"USER"})
     *
     * We were redirected here from PayPal after the buyer approved the payment
     *
     * @throws ConnectionException
     * @throws DBALException
     * @throws Exception
     */
    public function subscriptionProcess(array $params): string {
        try {
            // No `PayerId` is provided if there was an issue setting up
            // payment.
            FilterParams::required($params, 'PayerID');

            // Retrieve checkout info and complete the transaction.
            $payPalApiService = PayPalApiService::instance();
            $checkoutResponse = $payPalApiService->retrieveCheckoutInfo($params['token']);
            $doECResponse = $payPalApiService->completeSubscribeECTransaction($checkoutResponse);
            $subInfo = $payPalApiService->extractSubscriptionInfoFromCheckoutResponse($checkoutResponse);
            $payments = $payPalApiService->getCheckoutResponsePayments($doECResponse);

            $subscriptionType = SubscriptionsService::instance()->getSubscriptionType($subInfo['subscriptionId']);
            if (empty($subscriptionType)) {
                throw new Exception('Invalid subscription type.');
            }

            // The logged in user is the one buying the sub.
            $userService = UserService::instance();
            $userId = Session::getCredentials()->getUserId();
            $buyingUser = $userService->getUserById($userId);

            $db = Application::getDbConn();
            $dbTransactionInProgress = $db->beginTransaction();

            $paymentIds = [];
            if (count($payments) > 0) {
                foreach ($payments as $payment) {
                    $payment['payerId'] = $params['PayerID'];
                    $paymentId = OrdersService::instance()->addPayment($payment);
                    $paymentIds[] = $paymentId;
                }
            }

            $receivingUsers = [];
            if ($subInfo['purchaseType'] === SubPurchaseType::MASS_GIFT) {
                $receivingUsers = $this->pickMassGiftWinnersFromChat($subInfo['quantity'], $buyingUser);
            } else {
                $receivingUsers[] = !empty($subInfo['giftee']) ? $userService->getUserByUsername($subInfo['giftee']) : $buyingUser;
            }

            foreach ($receivingUsers as $receivingUser) {
                $this->createNewSubscription(
                    $subscriptionType,
                    $receivingUser,
                    $buyingUser,
                    $paymentIds,
                    $params['token'],
                    boolval($subInfo['recurring'] ?? '0')
                );
            }

            $db->commit();
        } catch (Exception $e) {
            if (!empty($db) && $dbTransactionInProgress ?? false) {
                $db->rollBack();
            }

            Log::critical("Error processing subscription. {$e}");
            return 'redirect: /subscription/error';
        }

        $broadcastMessage = Session::getAndRemove('broadcastMessage');
        $broadcastMessage = mb_substr(trim($broadcastMessage), 0, 250);

        $subscribeMessage = Session::getAndRemove('subscribeMessage');
        $subscribeMessage = mb_substr(trim($subscribeMessage), 0, 250);

        // Mass gifts have an additional alert.
        if ($subInfo['purchaseType'] === SubPurchaseType::MASS_GIFT) {
            $redisService = ChatRedisService::instance();

            $subWord = $subInfo['quantity'] == 1 ? 'sub' : 'subs';
            $redisService->sendBroadcast("{$buyingUser['username']} gifted {$subInfo['quantity']} {$subscriptionType['tierLabel']} {$subWord} to the community!");
            if ($broadcastMessage !== '') {
                $redisService->sendBroadcast("{$buyingUser['username']} said... $broadcastMessage");
            }

            StreamLabsService::instance()->sendMassGiftAlert(
                $subscriptionType,
                $broadcastMessage,
                $buyingUser['username'],
                $subInfo['quantity']
            );

            // Broadcast messages for mass gifts are printed with the mass gift
            // notification broadcast. We empty the value to ensure it isn't
            // printed for the direct gift alerts that follow.
            $broadcastMessage = '';
        }

        // Log the subscription event in Discord.
        if ($subscribeMessage !== '') {
            DiscordMessenger::send('New subscriber', [
                'fields' => [
                    ['title' => 'User', 'value' => DiscordMessenger::userLink($buyingUser['userId'], $buyingUser['username']), 'short' => false],
                    ['title' => 'Message', 'value' => $subscribeMessage, 'short' => false],
                ]
            ]);
        }

        foreach ($receivingUsers as $receivingUser) {
            $this->performPostSubscriptionActions($subscriptionType, $receivingUser, $buyingUser, $broadcastMessage);
        }

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

        $payPalApiService = PayPalApiService::instance();
        $checkoutResponse = $payPalApiService->retrieveCheckoutInfo($params['token']);
        $subInfo = $payPalApiService->extractSubscriptionInfoFromCheckoutResponse($checkoutResponse);
        $checkoutDetails = $checkoutResponse->GetExpressCheckoutDetailsResponseDetails;
        $paymentDetails = $checkoutDetails->PaymentDetails[0];

        $subscriptionType = SubscriptionsService::instance()->getSubscriptionType($subInfo['subscriptionId']);

        $model->title = 'Subscription Complete';
        // There is no `TransactionId` if the transaction is pending.
        $model->transactionId = $paymentDetails->TransactionId ?? null;
        $model->purchaseType = $subInfo['purchaseType'];
        $model->quantity = $subInfo['quantity'];
        $model->recurring = $subInfo['recurring'];
        $model->giftee = $subInfo['giftee'] ?? null;
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

    /**
     * Validate the parameters in a subscription request.
     *
     * @throws Exception
     */
    private function validateSubscriptionParameters(array $params) {
        $subscriptionsService = SubscriptionsService::instance();
        $subscriptionType = $subscriptionsService->getSubscriptionType($params['subscriptionId']);
        if (empty($subscriptionType)) {
            throw new Exception('Invalid subscription type.');
        }

        switch ($params['purchaseType']) {
            case SubPurchaseType::_SELF:
                if ($params['quantity'] != 1) {
                    throw new Exception('You can only buy one sub for yourself at a time.');
                }
                break;
            case SubPurchaseType::DIRECT_GIFT:
                FilterParams::required($params, 'giftee');

                $userId = Session::getCredentials()->getUserId();
                $giftReceiver = UserService::instance()->getUserByUsername($params['giftee']);

                if ($params['quantity'] != 1) {
                    throw new Exception('You can only gift one sub to a specific user at a time.');
                } else if (empty($giftReceiver)) {
                    throw new Exception('Invalid giftee: no such user exists.');
                } else if ($giftReceiver['userId'] === $userId) {
                    throw new Exception('Invalid giftee: you cannot gift yourself a sub.');
                } else if (!$subscriptionsService->canUserReceiveGift($userId, $giftReceiver['userId'])) {
                    throw new Exception('Invalid giftee: this user can\'t accept gift subs.');
                }
                break;
            case SubPurchaseType::MASS_GIFT:
                $isRecurring = boolval($params['recurring'] ?? '0');
                if ($params['quantity'] > 100 || $params['quantity'] < 1) {
                    throw new Exception('You can only mass gift between 1 and 100 subs.');
                } else if ($isRecurring) {
                    throw new Exception('A mass gift cannot be recurring.');
                }
                break;
            default:
                throw new Exception('Invalid sub purchase type.');
        }
    }

    /**
     * @throws Exception
     */
    private function createNewSubscription(array $subscriptionType, array $receivingUser, array $buyingUser, array $paymentIds, string $token, bool $recurring = false) {
        $subscriptionsService = SubscriptionsService::instance();
        $payPalApiService = PayPalApiService::instance();
        $ordersService = OrdersService::instance();

        // Create a new subscription.
        $startDate = Date::getDateTime();
        $endDate = Date::getDateTime();
        $endDate->modify("+{$subscriptionType['billingFrequency']} {$subscriptionType['billingPeriod']}");

        $subscription = [
            'userId'             => $receivingUser['userId'],
            'gifter'             => $receivingUser['userId'] !== $buyingUser['userId'] ? $buyingUser['userId'] : null,
            'subscriptionSource' => Config::$a['subscriptionType'],
            'subscriptionType'   => $subscriptionType['id'],
            'subscriptionTier'   => $subscriptionType['tier'],
            'createdDate'        => $startDate->format('Y-m-d H:i:s'),
            'endDate'            => $endDate->format('Y-m-d H:i:s'),
            'recurring'          => intval($recurring),
            'status'             => SubscriptionStatus::_NEW
        ];
        $subscription['subscriptionId'] = $subscriptionsService->addSubscription($subscription);

        // If there are no payments, assume the transaction is pending. We mark
        // the sub as pending until we receive an IPN from PayPal.
        if (!empty($paymentIds)) {
            $subscription['status'] = SubscriptionStatus::ACTIVE;
            foreach ($paymentIds as $paymentId) {
                $ordersService->addPurchaseOfSubscription($paymentId, $subscription['subscriptionId']);
            }
        } else {
            $subscription['status'] = SubscriptionStatus::PENDING;
        }

        // Create a recurring payment profile for recurring subs.
        if ($recurring) {
            $startPaymentDate = $startDate;
            // The next payment date is one day before the sub expires.
            $nextPaymentDate = (clone $endDate)->modify('-1 day');
            $reference = "{$receivingUser['userId']}-{$subscription['subscriptionId']}";

            $paymentProfileId = $payPalApiService->createSubscriptionPaymentProfile(
                $token,
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

        $subscriptionsService->updateSubscription([
            'subscriptionId' => $subscription['subscriptionId'],
            'paymentStatus' => $subscription['paymentStatus'] ?? null,
            'paymentProfileId' => $subscription['paymentProfileId'] ?? null,
            'billingStartDate' => $subscription['billingStartDate'] ?? null,
            'billingNextDate' => $subscription['billingNextDate'] ?? null,
            'status' => $subscription['status']
        ]);
    }

    /**
     * Unban the newly-subscribed user and display a sub alert in chat.
     */
    private function performPostSubscriptionActions(array $subscriptionType, array $receivingUser, array $buyingUser, string $broadcastMessage) {
        $redisService = ChatRedisService::instance();

        // Unban/unmute the newly-subscribed user.
        try {
            $chatBanService = ChatBanService::instance();
            $ban = $chatBanService->getUserActiveBan($receivingUser['userId']);
            if (empty($ban) || !$chatBanService->isPermanentBan($ban)) {
                $redisService->sendUnbanAndUnmute($receivingUser['userId']);
            }
        } catch (Exception $e) {
            Log::error('Error unbanning/unmuting user. ', $e->getMessage());
        }

        // Broadcast the subscription in chat and on stream.
        if ($receivingUser['userId'] !== $buyingUser['userId']) {
            $redisService->sendBroadcast("{$buyingUser['username']} gifted {$receivingUser['username']} a {$subscriptionType['tierLabel']} subscription!");
            if ($broadcastMessage !== '') {
                $redisService->sendBroadcast("{$buyingUser['username']} said... $broadcastMessage");
            }

            StreamLabsService::instance()->sendDirectGiftAlert(
                $subscriptionType,
                $broadcastMessage,
                $buyingUser['username'],
                $receivingUser['username']
            );
        } else {
            $redisService->sendBroadcast("{$receivingUser['username']} is now a {$subscriptionType['tierLabel']} subscriber!");
            if ($broadcastMessage !== '') {
                $redisService->sendBroadcast("{$receivingUser['username']} said... $broadcastMessage");
            }

            StreamLabsService::instance()->sendSubAlert(
                $subscriptionType,
                $broadcastMessage,
                $receivingUser['username']
            );
        }

        AuthenticationService::instance()->flagUserForUpdate($receivingUser['userId']);
    }

    /**
     * Returns an array of random users as winners of the gift subs. Only those
     * who accept gift subs and aren't currently subbed can qualify. If there is
     * an insufficient number of qualifying users among those connected to chat,
     * we pull from users who aren't in chat until we have enough.
     */
    private function pickMassGiftWinnersFromChat(int $quantity, array $buyingUser): array {
        $connectedUsers = ChatRedisService::instance()->getChatConnectedUsers();

        // Users who have the `subscriber` flair are already subscribed and
        // don't qualify. Exclude the buyer because they can't gift themselves a
        // sub.
        $qualifiedUsers = array_filter(
            $connectedUsers,
            function($user) use ($buyingUser) {
                return $user['nick'] !== $buyingUser['username'] && !in_array(UserFeature::SUBSCRIBER, $user['features']);
            }
        );
        $qualifiedUsernames = array_map(function($user) { return $user['nick']; }, $qualifiedUsers);
        $giftableUsers = SubscriptionsService::instance()->findGiftableUsersByUsernames($qualifiedUsernames);

        shuffle($giftableUsers);
        $winners = array_slice($giftableUsers, 0, $quantity);

        // If there aren't enough winners, we pull giftable, recently-modified
        // users who aren't in chat until there are.
        if (count($winners) < $quantity) {
            $numberNeeded = $quantity - count($winners);

            // Exclude users that already won.
            $userIdsToExclude = array_map(function($user) { return $user['userId']; }, $winners);
            $userIdsToExclude[] = $buyingUser['userId'];

            $moreWinners = SubscriptionsService::instance()->findRecentlyModifiedGiftableUsers($numberNeeded, $userIdsToExclude);
            $winners = array_merge($winners, $moreWinners);
        }

        // If there still aren't enough winners, then nearly every registered
        // user must be a sub. Pop open a bottle of champagne to celebrate the
        // streamer's success, process the mass gift anyway, and apologize to
        // the buyer/issue a refund if they notice they got scamazed.
        if (count($winners) < $quantity) {
            Log::critical(sprintf(
                '%s (ID: %d) mass gifted %d subs, but only %d qualifying recipients were found.',
                $buyingUser['username'],
                $buyingUser['userId'],
                $quantity,
                count($winners)
            ));
        }

        return $winners;
    }
}
