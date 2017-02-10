<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Request;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Config;
use Destiny\Common\Response;
use Destiny\Common\MimeType;
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
     * @Route ("/api/messages/send")
     * @HttpMethod ({"POST"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *     message=string
     *     userid=999
     *     targetuserid=999
     *
     * @param array $params
     * @return Response
     */
    public function sendMessage(array $params) {
        $privateMessageService = PrivateMessageService::instance();
        $chatIntegrationService = ChatIntegrationService::instance();
        $userService = UserService::instance();

        $response = new Response();
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
                throw new Exception ("privmsgbanned");

            $oldEnough = $userService->isUserOldEnough ( $params['userid'] );
            if (! $oldEnough)
                throw new Exception ("privmsgaccounttooyoung");

            $user = $userService->getUserById ( $params['userid'] );
            $credentials = new SessionCredentials ( $user );
            $credentials->addRoles ( $userService->getUserRolesByUserId ( $params['userid'] ) );
            $targetuser = $userService->getUserById ( $params['targetuserid'] );
            
            if(empty($targetuser))
                throw new Exception ('notfound');
                
            $canSend = $privateMessageService->canSend( $credentials, $params['targetuserid'] );
            if (! $canSend)
                throw new Exception ("throttled");

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
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
            $response->setBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
        return $response;
    }

    /**
     * @Route ("/api/twitchsubscriptions")
     * @HttpMethod ({"GET"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * @param array $params
     * @return Response
     */
    public function getSubscription(array $params) {
        $userService = UserService::instance();
        $response = new Response();
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');
            $response->setStatus(Http::STATUS_OK);
            $response->setBody(json_encode(['authids' => $userService->getActiveTwitchSubscriptions()]));
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            $response->setBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
        return $response;
    }

    /**
     * @Route ("/api/twitchsubscriptions")
     * @HttpMethod ({"POST"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     [{"123":1},{"456":0}]
     *
     * @param array $params
     * @param Request $request
     * @return Response
     */
    public function postSubscription(array $params, Request $request) {
        $response = new Response();
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
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            $response->setBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
        return $response;
    }

    /**
     * @Route ("/api/twitchresubscription")
     * @HttpMethod ({"POST"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     [{"123":1},{"456":0}]
     *
     * @param array $params
     * @param Request $request
     * @return Response
     */
    public function postReSubscription(array $params, Request $request) {
        $response = new Response();
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
            $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
            $response->setBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
        return $response;
    }

    /**
     * @Route ("/api/addtwitchsubscription")
     * @HttpMethod ({"POST"})
     *
     * Expects the following REQUEST params:
     *     privatekey=XXXXXXXX
     *
     * Expects the following body structure:
     *     {"nick":"username"}
     *
     * @param array $params
     * @param Request $request
     * @return Response
     */
    public function addSubscription(array $params, Request $request) {
        $userService = UserService::instance();
        $response = new Response();
        $response->addHeader(Http::HEADER_CONTENTTYPE, MimeType::JSON);
        try {
            FilterParams::required($params, 'privatekey');
            if(!$this->checkPrivateKey($params['privatekey']))
                throw new Exception ('Invalid shared private key.');

            $data = json_decode( $request->getBody(), true );
            FilterParams::required($data, 'nick');
            $authid = $userService->getTwitchIDFromNick( $data['nick'] );
            if ($authid !== false) {
                $this->updateSubsAndBroadcast([$authid => 1], "%s has resubscribed on Twitch!");
                $response->setBody(json_encode(['id' => $authid]));
            } else {
                $response->setBody(json_encode(['id' => '']));
            }
            
            $response->setStatus(Http::STATUS_OK);
        } catch (Exception $e) {
            $response->setStatus(Http::STATUS_BAD_REQUEST);
            $response->setBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
        return $response;
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
