<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Config;
use Destiny\Common\Exception;

/**
 * @method static ChatIntegrationService instance()
 */
class ChatIntegrationService extends Service {
    
    /**
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
     * @param SessionCredentials $credentials
     * @param string $sessionId         
     */
    public function setChatSession(SessionCredentials $credentials, $sessionId) {
        $redis = Application::instance ()->getRedis ();
        $json = json_encode ( $credentials->getData () );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
        $redis->set ( sprintf ( 'CHAT:session-%s', $sessionId ), $json, intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
        $redis->publish ( sprintf ( 'refreshuser-%s', Config::$a ['redis'] ['database'] ), $json );
        $redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
    }

    /**
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
     * @param string $sessionId
     */
    public function deleteChatSession($sessionId) {
        $redis = Application::instance ()->getRedis ();
        $redis->delete ( sprintf ( 'CHAT:session-%s', $sessionId ) );
    }

    /**
     * @param string $message
     *          the message
     * @return \stdClass
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
     * @param int $userId
     *          the userId
     * @return int
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
     * @throws Exception
     * @return array
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
     * @return int
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
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function publishPrivateMessage(array $data) {
        $data = array(
            'messageid' => $data['messageid'],
            'message' => $data['message'],
            'username' => $data['username'],
            'userid' => $data['userid'],
            'targetusername' => $data['targetusername'],
            'targetuserid' => $data['targetuserid']
        );
        $redis = Application::instance ()->getRedis ();
        return $redis->publish ( sprintf ( 'privmsg-%s', Config::$a ['redis'] ['database'] ), json_encode($data) );
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function publishPrivateMessages(array $data){
        $redis = Application::instance ()->getRedis ();
        $chunked = array_chunk($data, 100);
        foreach ($chunked as $chunk) {
            $redis->multi();
            foreach ($chunk as $msgdata) {
                $this->publishPrivateMessage( $msgdata );
            }
            $redis->exec();
        }
    }

    /**
     * @return array
     */
    public function getChatLog() {
        $redis = Application::instance ()->getRedis ();
        return $redis->lRange('CHAT:chatlog', 0, -1);
    }

    /**
     * Fetches top chat combos from the database by default.
     * However if "true" is passed as an param it will fetch the most recent
     * chat combos instead.
     *
     * @param bool $recent
     * @return mixed
     */
    public function getChatCombos($recent = false) {
        $conn = Application::instance ()->getConnection ();
        $order = ($recent == true) ? 'timestamp' : 'combo';
        $stmt = $conn->prepare("
            SELECT
                emote,
                combo,
                memers,
                timestamp
            FROM
                chat_combos
            ORDER BY $order DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetches top chat combos for the specified user.
     *
     * @param $username
     * @return mixed
     */
    public function getMyChatCombos($username) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT
                emote,
                combo,
                memers,
                timestamp
            FROM
                chat_combos
            WHERE
                memers LIKE '%$username%'
            ORDER BY combo DESC
            LIMIT 10
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Counts the number of rows in the chat_combos table
     *
     * @return mixed
     */
    public function countChatCombos() {
        $conn  = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM chat_combos
        ");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Removes all chat combos except 10 highest combos
     *
     * @return mixed
     */
    public function deleteChatCombos() {
        $conn  = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            DELETE FROM chat_combos
            WHERE id NOT IN
            (
                SELECT * FROM
                (
                    SELECT id
                    FROM chat_combos
                    ORDER BY combo DESC
                    LIMIT 10
                ) s
            )
        ");
        return $stmt->execute();
    }

    /**
     * Removes all of the chat combos
     *
     * @return mixed
     */
    public function purgeChatCombos() {
        $conn  = Application::instance ()->getConnection ();
        $redis = Application::instance ()->getRedis ();

        $stmt = $conn->prepare("
            TRUNCATE TABLE chat_combos
        ");
        return $stmt->execute();
    }

}