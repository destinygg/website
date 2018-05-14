<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Config;
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
class ChatApiController {

    const MSG_FMT_TWITCH_SUB = "%s is now a Twitch subscriber!";
    const MSG_FMT_TWITCH_RESUB = "%s has resubscribed on Twitch!";
    const MSG_FMT_TWITCH_RESUB_MONTHS = "%s has resubscribed on Twitch! active for %s months";
    const MSG_FMT_TWITCH_RESUB_MONTH = "%s has resubscribed on Twitch! active for %s month";

    /**
     * Check the private against the local configuration
     *
     * @param string $privatekey
     * @return boolean
     */
    protected function checkPrivateKey($privatekey){
        return Config::$a['privateKeys']['chat'] === $privatekey;
    }

    /**
     * @Route ("/api/messages/send")
     * @HttpMethod ({"POST"})
     * @ResponseBody
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
        $redisService = ChatRedisService::instance();
        $userService = UserService::instance();
        try {
            FilterParams::required($params, 'privatekey');
            FilterParams::required($params, 'message');
            FilterParams::required($params, 'userid');
            FilterParams::required($params, 'targetuserid');

            if(! $this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');

            if($params['userid'] == $params['targetuserid'])
                throw new Exception ('Cannot send messages to yourself.');

            $ban = $userService->getUserActiveBan ( $params['userid'] );
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
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * @param Response $response
     * @param array $params
     * @return array
     *
     * @throws DBALException
     */
    public function getSubscription(Response $response, array $params) {
        $userService = UserService::instance();
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');
            $response->setStatus(Http::STATUS_OK);
            return ['authids' => $userService->getActiveTwitchSubscriptions()];
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * @Route ("/api/twitchsubscriptions")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * @param array $params
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function postSubscription(array $params, Response $response, Request $request) {
        try {
            FilterParams::required($params, 'privatekey');
            if (!$this->checkPrivateKey($params['privatekey'])) {
                throw new Exception ('Invalid shared private key.');
            }
            $subs = json_decode($request->getBody(), true);
            if (is_array($subs) && count($subs) > 0) {
                $this->updateSubsAndBroadcast($subs, self::MSG_FMT_TWITCH_SUB);
            }
            $response->setStatus(Http::STATUS_NO_CONTENT);
        } catch (\Exception $e) {
            Log::error('Error posting subscriptions.', $e->getMessage());
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return null;
    }

    /**
     * Expects the following body structure:
     *     [{"123":1},{"456":0}]
     *
     *  Where the key is the twitch user id (auth.authDetail) and the value is whether
     *  the user is a subscriber or not
     *
     * @param array $subs
     * @param string $fmt
     * @return void
     *
     * @throws Exception
     * @throws DBALException
     */
    private function updateSubsAndBroadcast(array $subs, $fmt) {
        $redisService = ChatRedisService::instance();
        $authService = AuthenticationService::instance();
        $users = UserService::instance()->updateTwitchSubscriptions($subs);
        foreach ($users as $user) {
            $authService->flagUserForUpdate($user['userId']);
            if ($user['istwitchsubscriber']) {
                $redisService->sendBroadcast(sprintf($fmt, $user['username']));
            }
        }
    }

    /**
     * Newer way of posting a subscribe event, twitch pubsub golang project sends a http post to this endpoint
     * when it receives an event from the twitch pubsub websocket.
     * https://dev.twitch.tv/docs/pubsub#example-channel-subscriptions-event-message
     *
     * @Route ("/api/twitch/subscribe")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * @param array $params
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function twitchSubscribe(array $params, Response $response, Request $request) {
        try {
            FilterParams::required($params, 'privatekey');
            if (!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');

            $data = json_decode($request->getBody(), true);
            FilterParams::required($data, 'user_id');
            FilterParams::declared($data, 'months');

            $userService = UserService::instance();
            $user = $userService->getUserByAuthId($data['user_id'], 'twitch');

            if (!empty($user)) {
                $username = $user['username'];
                $message = !empty($data['sub_message']) ? $data['sub_message']['message'] : '';
                if ($user['istwitchsubscriber'] == 0) {
                    $userService->updateUser($user['userId'], ['istwitchsubscriber' => 1]);
                    $authService = AuthenticationService::instance();
                    $authService->flagUserForUpdate($user['userId']);
                }
                $broadcast = sprintf(self::MSG_FMT_TWITCH_SUB, $username);
                if (!empty($data['months']) && intval($data['months']) > 0) {
                    if (intval($data['months']) > 1) {
                        $broadcast = sprintf(self::MSG_FMT_TWITCH_RESUB_MONTHS, $username, $data['months']);
                    } else {
                        $broadcast = sprintf(self::MSG_FMT_TWITCH_RESUB_MONTH, $username, $data['months']);
                    }
                }
                $redisService = ChatRedisService::instance();
                $redisService->sendBroadcast($broadcast);
                if (!empty($message)) {
                    $redisService->sendBroadcast("$username said... $message");
                }
            }

            $response->setStatus(Http::STATUS_NO_CONTENT);
        } catch (\Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return null;
    }
}
