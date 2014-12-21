<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\User\UserService;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Response;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Messages\PrivateMessageService;
use Destiny\Common\SessionCredentials;

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
        return (Config::$a['chat']['privatekey'] === $privatekey);
    }

    /**
     * @Route ("/api/messages/send")
     * @HttpMethod ({"POST"})
     *
     * Expects the following GET|POST variables:
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
        $response = array();

        try {

            FilterParams::required($params, 'privatekey');
            $this->checkPrivateKey($params['privatekey']);

            FilterParams::required($params, 'message');
            FilterParams::required($params, 'userid');
            FilterParams::required($params, 'targetuserid');

            if($params['userid'] == $params['targetuserid']){
                throw new Exception ('Cannot send messages to yourself.');
            }

            $ban = $userService->getUserActiveBan ( $params['userid'] );
            if (! empty ( $ban )) {
                throw new Exception ("privmsgbanned");
            }

            $oldEnough = $userService->isUserOldEnough ( $params['userid'] );
            if (! $oldEnough) {
                throw new Exception ("privmsgaccounttooyoung");
            }

            $user = $userService->getUserById ( $params['userid'] );
            $credentials = new SessionCredentials ( $user );
            $credentials->addRoles ( $userService->getUserRolesByUserId ( $params['userid'] ) );
            $canSend = $privateMessageService->canSend( $credentials );
            if (! $canSend) {
                throw new Exception ("throttled");
            }

            $user = $userService->getUserById ( $params['userid'] );
            $targetuser = $userService->getUserById ( $params['targetuserid'] );

            if(empty($user))
                throw new Exception ('notfound');

            if(empty($targetuser))
                throw new Exception ('notfound');

            $message = array(
                'userid' => $params['userid'],
                'targetuserid' => $params['targetuserid'],
                'message' => $params['message'],
                'isread' => 0
            );

            $message['id'] = $privateMessageService->addMessage( $message );
            $chatIntegrationService->publishPrivateMessage( $message, $user, $targetuser );
            $response = new Response ( Http::STATUS_NO_CONTENT );

        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = $e->getMessage();
            $response = new Response ( Http::STATUS_BAD_REQUEST, json_encode ( $response ) );
            $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        }
        return $response;
    }
}