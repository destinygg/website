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
        $inbox = $privateMessageService->getInboxMessagesByUserId( $userId, 0 );
        $read = $privateMessageService->getInboxMessagesByUserId( $userId, 1 );

        $viewModel->inbox = $inbox;
        $viewModel->read = $read;
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
        $userId = Session::getCredentials ()->getUserId ();
        $privateMessageService = PrivateMessageService::instance();
        $chatIntegrationService = ChatIntegrationService::instance();
        $userService = UserService::instance();
        $response = array('success' => false, 'message' => '');
        $isReply = (isset($params['replyto']) && !empty($params['replyto']));

        try {

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

                $privateMessageService->addMessage($message);
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

            $message = $privateMessageService->getMessageByIdAndTargetUserId( $params['id'], $userId );
            if(empty($message)){
                throw new Exception('Invalid message');
            }
            $privateMessageService->openMessageById( $message['id'] );

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
        $message = $privateMessageService->getMessageByIdAndTargetUserIdOrUserId( $params['id'], $userId );
        if(empty($message)){
            throw new Exception('Invalid message id or user');
        }

        $messages = $privateMessageService->getMessagesBetweenUserIdAndTargetUserId( $message['userid'], $message['targetuserid'] );
        foreach ($messages as $msg) {
            if($msg['targetuserid'] == $userId && $msg['isread'] == 0){
                $privateMessageService->openMessageById( $msg['id'] );
            }
        }

        $viewModel->message = $message;
        $viewModel->messages = $messages;
        $viewModel->username = $username;
        $viewModel->userId = $userId;
        $viewModel->replyto = $message['id'];
        $viewModel->title = 'Message';
        return 'profile/message';
    }

    /**
     * @Route ("/profile/messages/users/{username}")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @return Response
     */
    public function userMessages(array $params, ViewModel $viewModel){
        FilterParams::required($params, 'username');

        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $username = Session::getCredentials ()->getUsername ();

        $targetUser = UserService::instance()->getUserByUsername($params['username']);
        if(empty($targetUser)){
            throw new Exception("Could not find target user");
        }

        $messages = $privateMessageService->getMessagesBetweenUserIdAndTargetUserId( $userId, $targetUser['userId'] );

        // @TODO make the UI handle no messages...
        if(count($messages) <= 0){
            throw new Exception('No messages for this user');
        }

        foreach ($messages as $msg) {
            if($msg['targetuserid'] == $userId && $msg['isread'] == 0){
                $privateMessageService->openMessageById( $msg['id'] );
            }
        }

        $message = $messages[count($messages)-1];

        $viewModel->message = $message;
        $viewModel->messages = $messages;
        $viewModel->username = $username;
        $viewModel->userId = $userId;
        $viewModel->replyto = $message['id'];
        $viewModel->title = 'Message';
        return 'profile/message';
    }

    /**
     * @Route ("/profile/messages/users/{username}/open")
     * @Secure ({"USER"})
     *
     * @param array $params
     * @return Response
     */
    public function openUserMessagesAsRead(array $params){
        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $response = array('success'=>true);

        try {
            FilterParams::required($params, 'username');
            $targetUser = UserService::instance()->getUserByUsername($params['username']);
            if(empty($targetUser)){
                throw new Exception("Could not find target user");
            }
            $privateMessageService->openMessagesByUserIdAndTargetUserId( $userId, $targetUser['userid'] );
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        $response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
        $response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
        return $response;
    }

}