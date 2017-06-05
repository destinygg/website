<?php
namespace Destiny\Controllers;

use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Request;
use Destiny\Common\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Config;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Messages\PrivateMessageService;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Authentication\AuthenticationService;

/**
 * @Controller
 */
class ChatApiController {

    /**
     * Check the private against the local configuration
     *
     * @param string $privatekey
     * @return boolean
     */
    protected function checkPrivateKey($privatekey){
        return (Config::$a['privateKeys']['chat'] === $privatekey);
    }

    /**
     * @Route ("/api/messages/{id}/open")
     * @Secure ({"USER"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     */
    public function openMessage(array $params) {
        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $result = ['success' => true, 'message' => '', 'unread' => 0];
        try {
            FilterParams::required($params, 'id');
            // could not find the message that is targeted at the user
            if(!$privateMessageService->markMessageRead( $params['id'], $userId ))
                throw new Exception('Invalid message');

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
        }
        $result['unread'] = $privateMessageService->getUnreadMessageCount($userId);
        return $result;
    }

    /**
     * @Route ("/api/messages/inbox")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     */
    public function unreadConversations(){
        $userId = Session::getCredentials ()->getUserId ();
        $privateMessageService = PrivateMessageService::instance();
        $conversations = $privateMessageService->getUnreadConversations($userId, 50);
        return $conversations;
    }

    /**
     * @Route ("/api/messages/{username}/unread")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     */
    public function unreadMessagesFrom(array $params){
        $userService = UserService::instance();
        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $targetuser = $userService->getUserByUsername($params['username']);
        $messages = $privateMessageService->getMessagesBetweenUserIdAndTargetUserId( $userId, $targetuser['userId'], 0, 10 );
        $privateMessageService->markMessagesRead( $userId, $targetuser['userId'] );
        return $messages;
    }

    /**
     * @Route ("/api/messages/{username}/unread")
     * @Secure ({"USER"})
     * @HttpMethod ({"DELETE"})
     * @ResponseBody
     *
     * @param array $params
     * @return string
     */
    public function markReadMessagesFrom(array $params){
        $userService = UserService::instance();
        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $targetuser = $userService->getUserByUsername($params['username']);
        $privateMessageService->markMessagesRead( $userId, $targetuser['userId'] );
        return 'success';
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
     */
    public function sendMessage(Response $response, array $params) {
        $privateMessageService = PrivateMessageService::instance();
        $chatIntegrationService = ChatIntegrationService::instance();
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
            $credentials->addRoles ( $userService->getUserRolesByUserId ( $params['userid'] ) );
            $targetuser = $userService->getUserById ( $params['targetuserid'] );

            if(empty($targetuser))
                throw new Exception ('notfound');

            $canSend = $privateMessageService->canSend( $credentials, $params['targetuserid'] );
            if (! $canSend)
                throw new Exception ('throttled');

            if(empty($user))
                throw new Exception ('notfound');

            $message = array(
                'userid' => $params['userid'],
                'targetuserid' => $params['targetuserid'],
                'message' => $params['message'],
                'isread' => 0
            );

            $message['id'] = $privateMessageService->addMessage( $message );
            $chatIntegrationService->publishPrivateMessage(array(
                'messageid' => $message['id'],
                'message' => $message['message'],
                'username' => $user['username'],
                'userid' => $user['userId'],
                'targetusername' => $targetuser['username'],
                'targetuserid' => $targetuser['userId']
            ));
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
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     [{"123":1},{"456":0}]
     *
     * @param array $params
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function postSubscription(array $params, Response $response, Request $request) {
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');

            $subs = json_decode($request->getBody(), true);
            if(is_array($subs) && count($subs) > 0) {
                $this->updateSubsAndBroadcast($subs, "%s is now a Twitch subscriber!");
            }

            $response->setStatus(Http::STATUS_NO_CONTENT);
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return null;
    }

    /**
     * @Route ("/api/twitchresubscription")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     [{"123":1},{"456":0}]
     *
     * @param array $params
     * @param Response $response
     * @param Request $request
     * @return array
     */
    public function postReSubscription(array $params, Response $response, Request $request) {
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');

            $subs = json_decode( $request->getBody(), true );
            if(is_array($subs) && count($subs) > 0) {
                $this->updateSubsAndBroadcast($subs, "%s has resubscribed on Twitch!");
            }

            $response->setStatus(Http::STATUS_NO_CONTENT);
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return null;
    }

    /**
     * @Route ("/api/addtwitchsubscription")
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     {"nick":"username"}
     *
     * @param Response $response
     * @param array $params
     * @param Request $request
     * @return array
     */
    public function addSubscription(Response $response, array $params, Request $request) {
        $userService = UserService::instance();
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');
            $data = json_decode( $request->getBody(), true );
            FilterParams::required($data, 'nick');
            $authid = $userService->getTwitchIDFromNick( $data['nick'] );
            $response->setStatus(Http::STATUS_OK);
            if ($authid !== false) {
                $this->updateSubsAndBroadcast([$authid => 1], "%s has resubscribed on Twitch!");
                return ['id' => $authid];
            } else {
                return ['id' => ''];
            }
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
     */
    private function updateSubsAndBroadcast(array $subs, $fmt) {
        $chatIntegrationService = ChatIntegrationService::instance();
        $authenticationService = AuthenticationService::instance();
        $users = UserService::instance()->updateTwitchSubscriptions($subs);
        foreach ($users as $user) {
            $authenticationService->flagUserForUpdate($user['userId']);
            if ($user['istwitchsubscriber']) {
                $chatIntegrationService->sendBroadcast(sprintf($fmt, $user['username']));
            }
        }
    }
}
