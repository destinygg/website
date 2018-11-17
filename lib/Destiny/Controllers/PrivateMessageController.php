<?php
namespace Destiny\Controllers;

use Destiny\Chat\ChatBanService;
use Destiny\Common\Annotation\ResponseBody;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\FilterParams;
use Destiny\Messages\PrivateMessageService;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\User\UserService;
use Destiny\Chat\ChatRedisService;
use Destiny\Common\SessionCredentials;
use Doctrine\DBAL\DBALException;

/**
 * @Controller
 */
class PrivateMessageController {

    /**
     * @Route ("/profile/messages")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param ViewModel $viewModel
     * @return string
     */
    public function profileInbox(ViewModel $viewModel) {
        $username = Session::getCredentials ()->getUsername ();
        $viewModel->username = $username;
        $viewModel->title = 'Messages';
        return 'profile/inbox';
    }

    /**
     * @Route ("/profile/messages/send")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * Expects the following GET|POST variables:
     *     message=string
     *     recipients[]=username|group
     *
     * @param array $params
     * @return array
     */
    public function profileSend(array $params) {
        $privateMessageService = PrivateMessageService::instance();
        $redisService = ChatRedisService::instance();
        $chatBanService = ChatBanService::instance();
        $userService = UserService::instance();
        $result = ['success' => false, 'message' => ''];
        try {

            FilterParams::required($params, 'message');
            FilterParams::isarray($params, 'recipients');

            $sessionCredentials = Session::getCredentials();
            $userId = $sessionCredentials->getUserId();
            $username = strtolower($sessionCredentials->getUsername());
            $user = $userService->getUserById($userId);
            $recipients = array_unique(array_map('strtolower', $params['recipients']));

            if (empty($recipients))
                throw new Exception('Invalid recipients list');

            if (count($recipients) === 1 && $recipients[0] == $username)
                throw new Exception('Cannot send messages to yourself.');

            // Remove the user if its in the list
            $recipients = array_diff($recipients, [$username]);

            $ban = $chatBanService->getUserActiveBan($userId);
            if (!empty($ban))
                throw new Exception ("You cannot send messages while you are banned.");

            $oldEnough = $userService->isUserOldEnough($userId);
            if (!$oldEnough)
                throw new Exception ("Your account is not old enough to send messages.");

            $recipients = $userService->getUserIdsByUsernames($recipients);

            if (empty($recipients))
                throw new Exception('Invalid recipient value(s)');

            if (count($recipients) > 20)
                throw new Exception('You may only send to maximum 20 users.');

            $credentials = new SessionCredentials ($user);
            foreach ($recipients as $recipientId) {
                $canSend = $privateMessageService->canSend($credentials, $recipientId);
                if (!$canSend)
                    throw new Exception ("You have sent too many messages, throttled.");

                $targetuser = $userService->getUserById($recipientId);
                $message = [
                    'userid' => $userId,
                    'targetuserid' => $recipientId,
                    'message' => $params['message'],
                    'isread' => 0
                ];
                $message['id'] = $privateMessageService->addMessage($message);
                $redisService->publishPrivateMessage([
                    'messageid' => $message['id'],
                    'message' => $message['message'],
                    'username' => $sessionCredentials->getUsername(), // non-lowercase
                    'userid' => $userId,
                    'targetusername' => $targetuser['username'],
                    'targetuserid' => $targetuser['userId']
                ]);
            }

            $result['message'] = 'Message sent';
            $result['success'] = true;

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @Route ("/profile/messages/{targetuserid}")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     *
     * @param array $params
     * @param ViewModel $viewModel
     * @return string
     *
     * @throws Exception
     * @throws DBALException
     */
    public function profileMessages(array $params, ViewModel $viewModel) {
        FilterParams::required($params, 'targetuserid');
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $username = Session::getCredentials ()->getUsername ();
        $targetuser = $userService->getUserById($params['targetuserid']);
        if(empty($targetuser))
            throw new Exception('Invalid user');
        $viewModel->targetuser = $targetuser;
        $viewModel->username = $username;
        $viewModel->userId = $userId;
        $viewModel->title = 'Message';
        return 'profile/message';
    }

    /**
     * @Route ("/api/messages/open")
     * @Secure ({"USER"})
     * @ResponseBody
     */
    public function messagesOpen() {
        $userId = Session::getCredentials()->getUserId();
        $privateMessageService = PrivateMessageService::instance();
        try {
            $privateMessageService->markAllMessagesRead($userId);
        } catch (DBALException $e) {
            Log::warn($e->getMessage());
            return ['false' => true];
        }
        return ['success' => true];
    }

    /**
     * @Route ("/api/messages/inbox")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     *
     * @throws DBALException
     */
    public function messagesInbox(array $params){
        $userId = Session::getCredentials ()->getUserId ();
        $privateMessageService = PrivateMessageService::instance();
        $start = isset($params['s']) ? intval($params['s']) : 0;
        return $privateMessageService->getMessagesInboxByUserId( $userId, $start, 25 );
    }

    /**
     * @Route ("/api/messages/unread")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     */
    public function messagesUnread(){
        $userId = Session::getCredentials ()->getUserId ();
        $privateMessageService = PrivateMessageService::instance();
        try {
            return $privateMessageService->getUnreadConversations($userId, 50);
        } catch (DBALException $e) {
            Log::error($e->getMessage());
        }
        return null;
    }

    /**
     * @Route ("/api/messages/usr/{username}/inbox")
     * @Secure ({"USER"})
     * @HttpMethod ({"GET"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     *
     * @throws DBALException
     */
    public function messagesUserInbox(array $params){
        $userService = UserService::instance();
        $privateMessageService = PrivateMessageService::instance();
        $userId = Session::getCredentials ()->getUserId ();
        $start = isset($params['s']) ? intval($params['s']) : 0;
        $targetuser = $userService->getUserByUsername($params['username']);
        $messages = $privateMessageService->getMessagesBetweenUserIdAndTargetUserId( $userId, $targetuser['userId'], $start, 10 );
        $privateMessageService->markMessagesRead( $userId, $targetuser['userId'] );
        return $messages;
    }

    /**
     * @Route ("/api/messages/msg/{id}/open")
     * @Secure ({"USER"})
     * @HttpMethod ({"POST"})
     * @ResponseBody
     *
     * @param array $params
     * @return array
     */
    public function messageOpen(array $params) {
        try {
            FilterParams::required($params, 'id');
            $privateMessageService = PrivateMessageService::instance();
            $userId = Session::getCredentials ()->getUserId ();
            if(!$privateMessageService->markMessageRead( intval($params['id']), $userId ))
                throw new Exception('Invalid message');
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

}
