<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\PrivateKey;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\DBException;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Response;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use Destiny\Messages\PrivateMessageService;

/**
 * @Controller
 */
class ChatSubscriptionController {

    /**
     * Chat server uses this when a user does the /whisper command
     *
     * @Route ("/api/messages/send")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     * @PrivateKey ({"chat"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *     message=string
     *     userid=999
     *     targetuserid=999
     */
    public function sendMessage(Response $response, array $params): array {
        $privateMessageService = PrivateMessageService::instance();
        $chatBanService = ChatBanService::instance();
        $redisService = ChatRedisService::instance();
        $userService = UserService::instance();
        try {
            FilterParams::required($params, 'privatekey');
            FilterParams::required($params, 'message');
            FilterParams::required($params, 'userid');
            FilterParams::required($params, 'targetuserid');

            if($params['userid'] == $params['targetuserid'])
                throw new Exception ('Cannot send messages to yourself.');

            $ban = $chatBanService->getUserActiveBan ( $params['userid'] );
            if (! empty ( $ban ))
                throw new Exception ('privmsgbanned');

            $oldEnough = $userService->isUserOldEnough ( $params['userid'] );
            if (! $oldEnough)
                throw new Exception ('privmsgaccounttooyoung');

            $user = $userService->getUserById ( $params['userid'] );
            $credentials = new SessionCredentials ( $user );
            $credentials->addRoles ( $userService->getRolesByUserId ( $params['userid'] ) );
            $targetuser = $userService->getUserById ( $params['targetuserid'] );

            if(empty($targetuser))
                throw new Exception ('notfound');

            $canSend = $privateMessageService->canSend( $credentials, $params['targetuserid'] );
            if (! $canSend)
                throw new Exception ('throttled');

            if(empty($user))
                throw new Exception ('notfound');

            $message = [
                'userid' => $params['userid'],
                'targetuserid' => $params['targetuserid'],
                'message' => $params['message'],
                'isread' => 0
            ];

            $message['id'] = $privateMessageService->addMessage($message);
            $redisService->publishPrivateMessage([
                'messageid' => $message['id'],
                'message' => $message['message'],
                'username' => $user['username'],
                'userid' => $user['userId'], // TODO
                'targetusername' => $targetuser['username'],
                'targetuserid' => $targetuser['userId']
            ]);
            $response->setStatus(Http::STATUS_NO_CONTENT);
            return [];
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/api/twitchsubscriptions")
     * @HttpMethod ({"GET"})
     * @ResponseBody
     * @PrivateKey ({"chat"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * @throws DBException
     */
    public function getSubscription(Response $response): array {
        $userService = UserService::instance();
        $response->setStatus(Http::STATUS_OK);
        return ['authids' => $userService->getActiveTwitchSubscriptions()];
    }

    /**
     * @Route ("/api/twitchsubscriptions")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     * @PrivateKey ({"chat"})
     *
     * @return array|null
     */
    public function postSubscription(Response $response, Request $request) {

        $subs = json_decode($request->getBody(), true);
        $redisService = ChatRedisService::instance();
        $authService = AuthenticationService::instance();
        $userService = UserService::instance();
        if (is_array($subs) && count($subs) > 0) {
            try {
                $users = $userService->updateTwitchSubscriptions($subs);
                foreach ($users as $user) {
                    $authService->flagUserForUpdate($user['userId']);
                    if ($user['istwitchsubscriber']) {
                        $redisService->sendBroadcast(sprintf("%s is now a Twitch subscriber!", $user['username']));
                    }
                }
            } catch (Exception $e) {
                Log::error('Error posting subscriptions. ' . $e->getMessage());
                $response->setStatus(Http::STATUS_BAD_REQUEST);
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
        $response->setStatus(Http::STATUS_NO_CONTENT);
        return null;
    }

    /**
     * Newer way of posting a subscribe event, twitch pubsub golang project sends a http post to this endpoint
     * when it receives an event from the twitch pubsub websocket.
     * https://dev.twitch.tv/docs/pubsub#example-channel-subscriptions-event-message
     * TODO currently only listen for `channel-subscribe-events-v1` events
     *
     * @Route ("/api/twitch/subscribe")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     * @PrivateKey ({"chat"})
     *
     * @return array|null
     */
    public function twitchSubscribe(Response $response, Request $request) {
        try {
            $data = json_decode($request->getBody(), true);
            FilterParams::required($data, 'context');
            FilterParams::required($data, 'user_id');

            $userService = UserService::instance();
            $userAuthService = UserAuthService::instance();
            $redisService = ChatRedisService::instance();
            $chatBanService = ChatBanService::instance();
            $submessage = isset($data['sub_message']) && !empty($data['sub_message']) ? $data['sub_message']['message'] : '';
            $months = isset($data['months']) && !empty($data['months']) && $data['months'] > 0 ? ($data['months'] > 1 ? ' active for ' . $data['months'] . ' months' : ' active for ' . $data['months'] . ' month') : '';

            $userId = $userAuthService->getUserIdByAuthIdAndProvider((string) $data['user_id'], AuthProvider::TWITCH);
            if (!$userId) {
                Log::info("Twitch sub on a non dgg user.", $data);
                $response->setStatus(Http::STATUS_NO_CONTENT);
                return null;
            }

            $user = $userService->getUserById((int) $userId);
            switch (strtoupper($data['context'])) {

                case 'SUB':
                    $this->updateUserTwitchSub($user['userId']);
                    $redisService->sendBroadcast($user['username'] . " has subscribed on Twitch!");
                    if (!empty($submessage)) {
                        $redisService->sendBroadcast($user['username'] . " said... $submessage");
                    }
                    break;

                case 'RESUB':
                    $this->updateUserTwitchSub($user['userId']);
                    $redisService->sendBroadcast($user['username'] . " has resubscribed on Twitch!$months");
                    if (!empty($submessage)) {
                        $redisService->sendBroadcast($user['username'] . " said... $submessage");
                    }
                    break;

                case 'SUBGIFT':
                    FilterParams::required($data, 'recipient_id');
                    $recipientId = $userAuthService->getUserIdByAuthIdAndProvider((string) $data['recipient_id'], AuthProvider::TWITCH);
                    if ($recipientId) {
                        $this->updateUserTwitchSub($recipientId);
                        $recipient = $userService->getUserById($recipientId);
                        $redisService->sendBroadcast($user['username'] . " has gifted " . $recipient['username'] . " a Twitch subscription!$months");
                        if (!empty($submessage)) {
                            $redisService->sendBroadcast($user['username'] . " said... $submessage");
                        }
                    } else {
                        Log::info("Twitch sub gifted to a non dgg user.", $data);
                    }
                    break;

            }

            try {
                $ban = $chatBanService->getUserActiveBan($user['userId']);
                if (empty($ban) || !$chatBanService->isPermanentBan($ban)) {
                    $redisService->sendUnbanAndUnmute($user['userId']);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }

            $response->setStatus(Http::STATUS_NO_CONTENT);
            return null;
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Set a users twitch flag to 1 and "flag" user for update.
     * @throws DBException
     */
    private function updateUserTwitchSub(int $userId) {
        UserService::instance()->updateUser($userId, ['istwitchsubscriber' => 1]);
        AuthenticationService::instance()->flagUserForUpdate($userId);
    }
}
