<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Doctrine\DBAL\DBALException;
use \Redis;

/**
 * @method static ChatIntegrationService instance()
 */
class ChatIntegrationService extends Service {

    /**
     * @return Redis|null
     */
    public function getRedis() {
        return Application::instance()->getRedis();
    }
    
    /**
     * @param string $sessionId
     * @return void
     */
    public function renewChatSessionExpiration($sessionId) {
        $redis = $this->getRedis();
        $redis->expire(sprintf('CHAT:session-%s', $sessionId), intval(ini_get('session.gc_maxlifetime')));
    }

    /**
     * @param SessionCredentials $credentials
     * @param string $sessionId         
     */
    public function setChatSession(SessionCredentials $credentials, $sessionId) {
        $redis = $this->getRedis();
        $json = json_encode($credentials->getData());
        $redis->set(sprintf('CHAT:session-%s', $sessionId), $json, intval(ini_get('session.gc_maxlifetime')));
        $redis->publish(sprintf('refreshuser-%s', Config::$a ['redis'] ['database']), $json);
    }

    /**
     * @param SessionCredentials $credentials
     */
    public function refreshChatUserSession(SessionCredentials $credentials) {
        $redis = $this->getRedis();
        $json = json_encode($credentials->getData());
        $redis->publish(sprintf('refreshuser-%s', Config::$a ['redis'] ['database']), $json);
    }

    /**
     * @param string $sessionId
     */
    public function deleteChatSession($sessionId) {
        $redis = $this->getRedis();
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
        $redis = $this->getRedis();
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
        $redis = $this->getRedis();
        $redis->publish ( sprintf ( 'unbanuserid-%s', Config::$a ['redis'] ['database'] ), (string) $userId );
        return $userId;
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getActiveBans() {
        $conn = Application::getDbConn();
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
     * @throws DBALException
     */
    public function purgeBans() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('TRUNCATE TABLE bans');
        $stmt->execute();
        $redis = $this->getRedis();
        return $redis->publish ( sprintf ( 'refreshbans-%s', Config::$a ['redis'] ['database'] ), 'doesnotmatter' );
    }

    /**
     * @param array $data
     * @return int
     */
    public function publishPrivateMessage(array $data) {
        $data = [
            'messageid' => $data['messageid'],
            'message' => $data['message'],
            'username' => $data['username'],
            'userid' => $data['userid'],
            'targetusername' => $data['targetusername'],
            'targetuserid' => $data['targetuserid']
        ];
        $redis = $this->getRedis();
        return $redis->publish(sprintf('privmsg-%s', Config::$a ['redis'] ['database']), json_encode($data));
    }

    /**
     * @param array $data
     */
    public function publishPrivateMessages(array $data){
        $redis = $this->getRedis();
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
        $redis = $this->getRedis();
        return $redis->lRange('CHAT:chatlog', 0, -1);
    }
}