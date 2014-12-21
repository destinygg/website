<?php
namespace Destiny\Chat;

use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\User\UserService;

class ChatIntegrationService extends Service {
    
    /**
     * Singleton instance
     *
     * var ChatIntegrationService
     */
    protected static $instance = null;

    /**
     * Singleton instance
     *
     * @return ChatIntegrationService
     */
    public static function instance() {
        return parent::instance ();
    }

    /**
     * Refreshes the current users session timeout
     *
     * @param string $sessionId         
     * @return void
     */
    public function renewChatSessionExpiration($sessionId) {
        if (! empty ( $sessionId )) {
            $redis = Application::instance ()->getRedis ();
            $redis->expire ( sprintf ( 'CHAT:session-%s', $sessionId ), intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
        }
    }

    /**
     * Handle the update of the credentials for chat
     *
     * @param SessionCredentials $credentials           
     * @param string $sessionId         
     */
    public function setChatSession(SessionCredentials $credentials, $sessionId) {
        $redis = Application::instance ()->getRedis ();
        $json = json_encode ( $credentials->getData () );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
        $redis->set ( sprintf ( 'CHAT:session-%s', $sessionId ), $json, intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
    }

    /**
     * Update a users session
     *
     * @param SessionCredentials $credentials           
     */
    public function refreshChatUserSession(SessionCredentials $credentials) {
        $redis = Application::instance ()->getRedis ();
        $json = json_encode ( $credentials->getData () );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
        $redis->publish ( sprintf ( 'refreshuser-%s', Config::$a ['redis'] ['database'] ), $json );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
    }

    /**
     * Delete the session for the chat user
     */
    public function deleteChatSession() {
        $redis = Application::instance ()->getRedis ();
        $redis->delete ( sprintf ( 'CHAT:session-%s', Session::getSessionId () ) );
    }

    /**
     * Broadcast a message
     *
     * @param string $message
     *          the message
     * @throws Exception
     */
    public function sendBroadcast($message) {
        if (empty ( $message )) {
            throw new Exception ( 'Message required' );
        }
        $broadcast = new \stdClass ();
        $broadcast->data = $message;
        $redis = Application::instance ()->getRedis ();
        $redis->publish ( sprintf ( 'broadcast-%s', Config::$a ['redis'] ['database'] ), json_encode ( $broadcast ) );
        return $broadcast;
    }

    /**
     * Unban and unmute a userId
     *
     * @param int $userId
     *          the userId
     * @throws Exception
     */
    public function sendUnban($userId) {
        if (!$userId) {
            throw new Exception ( 'UserId required' );
        }
        $redis = Application::instance ()->getRedis ();
        $redis->publish ( sprintf ( 'unbanuserid-%s', Config::$a ['redis'] ['database'] ), (string) $userId );
        return $userId;
    }

    /**
     * Gets an array of bans
     *
     * @throws Exception
     */
    public function getActiveBans() {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT
                b.id AS banid,
                b.starttimestamp,
                b.endtimestamp,
                b.reason,
                b.ipaddress,
                b.targetuserid AS targetuserid,
                tu.username AS targetusername,
                b.userid AS banninguserid,
                u.username AS banningusername
            FROM
                bans AS b,
                dfl_users AS tu,
                dfl_users AS u
            WHERE
                (
                    b.endtimestamp IS NULL OR
                    b.endtimestamp >= NOW()
                ) AND
                b.userid       = u.userId AND
                b.targetuserid = tu.userId
            GROUP BY b.starttimestamp
            ORDER BY b.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Removes all of the bans and notifies the chat to refresh the bans
     * so it actually notices the bans being removed
     *
     * @throws Exception
     */
    public function purgeBans() {
        $conn  = Application::instance ()->getConnection ();
        $redis = Application::instance ()->getRedis ();

        $stmt = $conn->prepare("
            TRUNCATE TABLE bans
        ");
        $stmt->execute();
        return $redis->publish ( sprintf ( 'refreshbans-%s', Config::$a ['redis'] ['database'] ), "doesnotmatter" );
    }

    /**
     * Publish to the private message channel
     *
     * @param array $message
     * @param array $user
     * @param array $targetuser
     * @throws Exception
     */
    public function publishPrivateMessage(array $message, array $user, array $targetuser) {
        $data = array(
            'messageid' => $message['id'],
            'message' => $message['message'],
            'username' => $user['username'],
            'userid' => $user['userId'],
            'targetusername' => $targetuser['username'],
            'targetuserid' => $targetuser['userId']
        );
        $redis = Application::instance ()->getRedis ();
        return $redis->publish ( sprintf ( 'privmsg-%s', Config::$a ['redis'] ['database'] ), json_encode($data) );
    }
}