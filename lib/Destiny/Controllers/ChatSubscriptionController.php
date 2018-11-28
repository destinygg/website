<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Common\Annotation\PrivateKey;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Chat\ChatRedisService;
use Destiny\Messages\PrivateMessageService;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Authentication\AuthenticationService;
use Doctrine\DBAL\DBALException;

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
     *
     * @param Response $response
     * @param array $params
     * @return array
     *
     * @throws DBALException
     */
    public function sendMessage(Response $response, array $params) {
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

            $message['id'] = $privateMessageService->addMessage( $message );
            $redisService->publishPrivateMessage([
                'messageid' => $message['id'],
                'message' => $message['message'],
                'username' => $user['username'],
                'userid' => $user['userId'],
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
     * @param Response $response
     * @return array
     *
     * @throws DBALException
     */
    public function getSubscription(Response $response) {
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
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function postSubscription(Response $response, Request $request) {
        try {
            $subs = json_decode($request->getBody(), true);
            $redisService = ChatRedisService::instance();
            $authService = AuthenticationService::instance();
            $userService = UserService::instance();
            if (is_array($subs) && count($subs) > 0) {
                $users = $userService->updateTwitchSubscriptions($subs);
                foreach ($users as $user) {
                    $authService->flagUserForUpdate($user['userId']);
                    if ($user['istwitchsubscriber']) {
                        $redisService->sendBroadcast(sprintf("%s is now a Twitch subscriber!", $user['username']));
                    }
                }
            }
            $response->setStatus(Http::STATUS_NO_CONTENT);
        } catch (\Exception $e) {
            Log::error('Error posting subscriptions. ' . $e->getMessage());
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function twitchSubscribe(Response $response, Request $request) {
        try {
            $data = json_decode($request->getBody(), true);
            FilterParams::required($data, 'context');
            FilterParams::required($data, 'user_id');

            $redisService = ChatRedisService::instance();
            $submessage = isset($data['sub_message']) && !empty($data['sub_message']) ? $data['sub_message']['message'] : '';
            $months = isset($data['months']) && !empty($data['months']) && $data['months'] > 0 ? ($data['months'] > 1 ? ' active for ' . $data['months'] . ' months' : ' active for ' . $data['months'] . ' month') : '';
            $user = $this->getTwitchUserByAuthId($data['user_id']);

            switch (strtoupper($data['context'])) {

                case 'SUB':
                    $this->updateUserTwitchSub($user);
                    $redisService->sendBroadcast($user['username'] . " has subscribed on Twitch!");
                    if (!empty($submessage)) {
                        $redisService->sendBroadcast($user['username'] . " said... $submessage");
                    }
                    break;

                case 'RESUB':
                    $this->updateUserTwitchSub($user);
                    $redisService->sendBroadcast($user['username'] . " has resubscribed on Twitch!$months");
                    if (!empty($submessage)) {
                        $redisService->sendBroadcast($user['username'] . " said... $submessage");
                    }
                    break;

                case 'SUBGIFT':
                    FilterParams::required($data, 'recipient_id');
                    $recipient = $this->getTwitchUserByAuthId($data['recipient_id']);
                    $this->updateUserTwitchSub($recipient);
                    $redisService->sendBroadcast($user['username'] . " has gifted ". $recipient['username'] ." a Twitch subscription!$months");
                    if (!empty($submessage)) {
                        $redisService->sendBroadcast($user['username'] . " said... $submessage");
                    }
                    break;

            }
            $response->setStatus(Http::STATUS_NO_CONTENT);
            return null;
        } catch (\Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Set a users twitch flag to 1 and "flag" user for update.
     *
     * @param array $user
     * @throws DBALException
     */
    private function updateUserTwitchSub(array $user) {
        $userService = UserService::instance();
        $authService = AuthenticationService::instance();
        $userService->updateUser($user['userId'], ['istwitchsubscriber' => 1]);
        $authService->flagUserForUpdate($user['userId']);
    }

    /**
     * Get a user by twitch auth id (twitch user id)
     * If one is not found, throw an exception
     * @param $authId
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    private function getTwitchUserByAuthId($authId) {
        $userService = UserService::instance();
        $user = $userService->getAuthByIdAndProvider($authId, 'twitch');
        if (empty($user)) {
            throw new Exception('Invalid user');
        }
        return $user;
    }
}
