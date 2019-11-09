<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Redis\RedisUtils;
use Redis;

/**
 * @method static ChatRedisService instance()
 */
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

    function afterConstruct() {
        parent::afterConstruct();
        $this->maxlife = intval(ini_get('session.gc_maxlifetime'));
        $this->redisdb = Config::$a['redis']['database'];
        $this->redis = Application::instance()->getRedis();
    }

    private function stripRedisUserIpPrefixes(array $keys) {
        return array_filter(array_map(function($n) {
            return intval(substr($n, strlen('CHAT:userips-')));
        }, $keys), function($n){
            return $n != null && $n > 0;
        });
    }

    /**
     * Finds all users who share the same IP
     * @throws Exception
     */
    public function findUserIdsByUsersIp(int $userid): array {
        $keys = RedisUtils::callScript('check-sameip-users', [$userid]);
        return $this->stripRedisUserIpPrefixes($keys);
    }

    /**
     * Find all users by ip
     * @throws Exception
     */
    public function findUserIdsByIP(string $ipaddress): array {
        $keys = RedisUtils::callScript('check-ip', [$ipaddress]);
        return $this->stripRedisUserIpPrefixes($keys);
    }

    /**
     * Find all users by ip (wildcard)
     * @throws Exception
     */
    public function findUserIdsByIPWildcard(string $ipaddress): array {
        $keys = RedisUtils::callScript('check-ip-wildcard', [$ipaddress]);
        return $this->stripRedisUserIpPrefixes($keys);
    }

    /**
     * @return array $ipaddresses The addresses found
     */
    public function getIPByUserId(int $userid): array {
        $redis = Application::instance()->getRedis();
        return $redis->zRange('CHAT:userips-' . $userid, 0, -1);
    }
    
    /**
     * Updates the session ttl so it does not expire
     */
    public function renewChatSessionExpiration(string $sessionId) {
        $this->redis->expire("CHAT:session-$sessionId", $this->maxlife);
    }

    public function setChatSession(SessionCredentials $credentials, string $sessionId) {
        $this->redis->set("CHAT:session-$sessionId", json_encode($credentials->getData()), $this->maxlife);
    }

    public function removeChatSession(string $sessionId): int {
        return $this->redis->delete("CHAT:session-$sessionId");
    }

    public function sendRefreshUser(SessionCredentials $credentials): int {
        return $this->redis->publish("refreshuser-$this->redisdb", json_encode($credentials->getData()));
    }

    public function sendBroadcast(string $message): int {
        return $this->redis->publish("broadcast-$this->redisdb", json_encode(['data' => $message], JSON_FORCE_OBJECT));
    }

    public function sendUnban(int $userId) {
        $this->redis->publish("unbanuserid-$this->redisdb", "$userId");
    }

    /**
     * Notifies the chat to refresh the bans
     * so it actually notices the bans being removed
     */
    public function sendPurgeBans() {
        $this->redis->publish("refreshbans-$this->redisdb", 'doesnotmatter');
    }

    public function publishPrivateMessage(array $d): int {
        return $this->redis->publish("privmsg-$this->redisdb", json_encode([
            'messageid' => "{$d['messageid']}",
            'message' => $d['message'],
            'username' => $d['username'],
            //'userid' => $d['userid'],
            //'targetusername' => $d['targetusername'],
            'targetuserid' => "{$d['targetuserid']}"
        ]));
    }

    public function getChatLog(): array {
        return $this->redis->lRange('CHAT:chatlog', 0, -1);
    }
}