<?php
namespace Destiny\Controllers;

use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Response;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\FilterParams;
use Destiny\Messages\PrivateMessageService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Chat\ChatIntegrationService;

/**
 * @Controller
 */
class PrivateMessageController {

    /**
     * @Route ("/profile/messages")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     */
    public function inbox(array $params, ViewModel $viewModel) {
        $userId = Session::getCredentials ()->getUserId ();
        $username = Session::getCredentials ()->getUsername ();

        $privateMessageService = PrivateMessageService::instance();
        $inbox = $privateMessageService->getInboxMessagesByUserId( $userId );

        $viewModel->inbox = $inbox;
        $viewModel->username = $username;
        $viewModel->title = 'Messages';
        return 'profile/inbox';
    }

    /**
     * @Route ("/profile/messages/send")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     *
     * Expects the following GET|POST variables:
     *     message=string
     *     recipients[]=username|group
     *     replyto=message.id [Optional, if set, recipients are ignored]
     *
     * @param array $params
     * @return Response
     */
    public function sendMessage(array $params) {
        $user = Session::getCredentials ();
        $userId = $user->getUserId ();
        $privateMessageService = PrivateMessageService::instance();
        $chatIntegrationService = ChatIntegrationService::instance();
        $userService = UserService::instance();
        $response = array('success' => false, 'message' => '');
        $isReply = (isset($params['replyto']) && !empty($params['replyto']));

        try {

            $ban = $userService->getUserActiveBan ( $userId );
            if (! empty ( $ban )) {
                throw new Exception ("You cannot send messages while you are banned.");
            }

            $oldEnough = $userService->isUserOldEnough ( $userId );
            if (! $oldEnough) {
                throw new Exception ("Your account is not old enough to send messages.");
            }

            $canSend = $privateMessageService->canSend( $user );
            if (! $canSend) {
                throw new Exception ("You have sent too many messages, throttled.");
            }

            FilterParams::required($params, 'message');

            if($isReply){

                $replymessage = $privateMessageService->getMessageByIdAndTargetUserIdOrUserId( $params['replyto'], $userId );
                if(empty($replymessage)){
                    throw new Exception('Invalid reply to message');
                }
                
                $message = array(
                    'message' => $params['message'],
                    'isread' => 0
                );
                
                if($userId == $replymessage['userid']){
                    $message['userid'] = $replymessage['userid'];
                    $message['targetuserid'] = $replymessage['targetuserid'];
                }else{
                    $message['userid'] = $replymessage['targetuserid'];
                    $message['targetuserid'] = $replymessage['userid'];
                }

                $user = $userService->getUserById ( $message['userid'] );
                $targetuser = $userService->getUserById ( $message['targetuserid'] );

                $message['id'] = $privateMessageService->addMessage($message);
                $chatIntegrationService->publishPrivateMessage( $message, $user, $targetuser );

            }else{


                FilterParams::isarray($params, 'recipients');
                $recipients = $privateMessageService->prepareRecipients( $params['recipients'] );

                $user = $userService->getUserById ( $userId );
                foreach ($recipients as $recipientId) {
                    $targetuser = $userService->getUserById ( $recipientId );
                    $message = array(
                        'userid' => $userId,
                        'targetuserid' => $recipientId,
                        'message' => $params['message'],
                        'isread' => 0
                    );
                    $message['id'] = $privateMessageService->addMessage( $message );
                    $chatIntegrationService->publishPrivateMessage( $message, $user, $targetuser );
                }
                
            }

            $response['message'] = 'Message sent';
            $response['success'] = true;

        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/profile/messages/{id}/open")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return Response
     */
    public function openMessage(array $params) {

        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $response = array('success'=>true);

        try {
            FilterParams::required($params, 'id');

            // could not find the message that is targeted at the user
            if(!$privateMessageService->markMessageRead( $params['id'], $userId ))
                throw new Exception('Invalid message');

        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

    /**
     * @Route ("/profile/messages/{id}")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     */
    public function message(array $params, ViewModel $viewModel) {
        FilterParams::required($params, 'id');

        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $username = Session::getCredentials ()->getUsername ();

        $messages = $privateMessageService->getMessagesBetweenUserIdAndTargetUserId( $userId, $params['id'] );
        // mark messages that are meant for me as read, not the other way around
        $privateMessageService->markMessagesRead( $userId, $params['id'] );
        foreach($messages as $message) {
            if ($message['userid'] == $params['id'])
                break;
        }

        $viewModel->message = $message;
        $viewModel->messages = $messages;
        $viewModel->username = $username;
        $viewModel->userId = $userId;
        $viewModel->replyto = $message['id'];
        $viewModel->title = 'Message';
        return 'profile/message';
    }
}