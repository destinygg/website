<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\Config;
use Destiny\Redis\RedisUtils;
use \Redis;

class ChatRedisService extends Service {

    /**
     * @var integer
     */
    public $redisdb;

    /**
     * @var integer
     */
    public $maxlife;

    /**
     * @var Redis
     */
    public $redis;

    /**
     * @return ChatRedisService
     */
    public static function instance() {
        $inst = parent::instance();
        $inst->maxlife = intval(ini_get('session.gc_maxlifetime'));
        $inst->redisdb = Config::$a['redis']['database'];
        $inst->redis = Application::instance()->getRedis();
        return $inst;
    }

    /**
     * @param int $userid
     * @return array $users The users found
     * @throws Exception
     */
    public function findUserIdsByUsersIp($userid) {
        $keys = RedisUtils::callScript('check-sameip-users', [$userid]);
        return array_filter(array_map(function($n) {
            return intval(substr($n, strlen('CHAT:userips-')));
        }, $keys), function($n){
            return $n != null && $n > 0;
        });
    }

    /**
     * @param string $ipaddress
     * @return array $users The users found
     * @throws Exception
     */
    public function findUserIdsByIP($ipaddress) {
        $keys = RedisUtils::callScript('check-ip', [$ipaddress]);
        return array_filter(array_map(function($n) {
            return intval(substr($n, strlen('CHAT:userips-')));
        }, $keys), function($n){
            return $n != null && $n > 0;
        });
    }

    /**
     * @param int $userid
     * @return array $ipaddresses The addresses found
     */
    public function getIPByUserId($userid) {
        $redis = Application::instance()->getRedis();
        return $redis->zRange('CHAT:userips-' . $userid, 0, -1);
    }
    
    /**
     * Updates the session ttl so it does not expire
     * @param string $sessionId
     * @return void
     */
    public function renewChatSessionExpiration($sessionId) {
        $this->redis->expire("CHAT:session-$sessionId", $this->maxlife);
    }

    /**
     * @param SessionCredentials $credentials
     * @param string $sessionId         
     */
    public function setChatSession(SessionCredentials $credentials, $sessionId) {
        $this->redis->set("CHAT:session-$sessionId", json_encode($credentials->getData()), $this->maxlife);
    }

    /**
     * @param string $sessionId
     */
    public function removeChatSession($sessionId) {
        $this->redis->delete("CHAT:session-$sessionId");
    }

    /**
     * @param SessionCredentials $credentials
     */
    public function sendRefreshUser(SessionCredentials $credentials) {
        $this->redis->publish("refreshuser-$this->redisdb", json_encode($credentials->getData()));
    }

    /**
     * @param string $message
     */
    public function sendBroadcast($message) {
        $this->redis->publish("broadcast-$this->redisdb", json_encode(['data' => $message], JSON_FORCE_OBJECT));
    }

    /**
     * @param int $userId
     *          the userId
     */
    public function sendUnban($userId) {
        $this->redis->publish("unbanuserid-$this->redisdb", (string)$userId);
    }

    /**
     * Notifies the chat to refresh the bans
     * so it actually notices the bans being removed
     */
    public function sendPurgeBans() {
        $this->redis->publish("refreshbans-$this->redisdb", 'doesnotmatter');
    }

    /**
     * @param array $d
     * @return int
     */
    public function publishPrivateMessage(array $d) {
        return $this->redis->publish("privmsg-$this->redisdb", json_encode([
            'messageid' => $d['messageid'],
            'message' => $d['message'],
            'username' => $d['username'],
            'userid' => $d['userid'],
            'targetusername' => $d['targetusername'],
            'targetuserid' => $d['targetuserid']
        ]));
    }

    /**
     * @param array $data
     */
    public function publishPrivateMessages(array $data){
        foreach (array_chunk($data, 100) as $chunk) {
            $this->redis->multi();
            foreach ($chunk as $msgdata) {
                $this->publishPrivateMessage($msgdata);
            }
            $this->redis->exec();
        }
    }

    /**
     * @return array
     */
    public function getChatLog() {
        return $this->redis->lRange('CHAT:chatlog', 0, -1);
    }
}