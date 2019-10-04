<?php
namespace Destiny\Common\Authentication;

use DateInterval;
use Destiny\Chat\ChatRedisService;
use Destiny\Chat\EmoteService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Session\Session;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\CryptoOpenSSL;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;

/**
 * @method static AuthenticationService instance()
 */
class AuthenticationService extends Service {

    const REGEX_VALID_USERNAME = '/^[A-Za-z0-9_]{3,20}$/';
    const REGEX_REPLACE_CHAR_USERNAME = '/[^A-Za-z0-9_]/';
    const USERNAME_MIN = 3;
    const USERNAME_MAX = 20;

    /**
     * @throws Exception
     */
    public function validateUsername(string $username) {
        if (empty($username)) {
            throw new Exception ('Username required');
        }
        if (preg_match(self::REGEX_VALID_USERNAME, $username) == 0) {
            throw new Exception ('Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.');
        }
        if (preg_match_all('/[0-9]{3}/', $username, $m) > 0) {
            throw new Exception ('Too many numbers in a row in username');
        }
        if (preg_match_all('/[_]{2}/', $username, $m) > 0 || preg_match_all("/[_]+/", $username, $m) > 2) {
            throw new Exception ('Too many underscores in username');
        }
        if (preg_match_all("/[0-9]/", $username, $m) > round(strlen($username) / 2)) {
            throw new Exception ('Number ratio is too high in username');
        }

        $normalizeduname = strtolower($username);
        $front = substr($normalizeduname, 0, 2);

        // nick blacklists
        $blacklist = array_merge([], include _BASEDIR . '/config/nick.blacklist.php');
        if (in_array($normalizeduname, $blacklist)) {
            throw new Exception ('Username is blacklisted');
        }

        // nick-to-emote similarity heuristics, not perfect sadly ;(
        foreach ($this->getEmotesForValidation() as $v) {
            $prefix = $v['prefix'];
            $emote = strtolower($v['prefix']);
            if (strcasecmp($normalizeduname, $emote) === 0) {
                throw new Exception ("Username too similar to emote $prefix, try changing the first characters");
            }
            if (strpos($normalizeduname, $emote) === 0) {
                throw new Exception ("Username cannot start with emote $prefix, try changing the first characters");
            }
            if ($emote == 'lul') { // TODO remove this static reference
                continue;
            }
            $shortuname = substr($normalizeduname, 0, strlen($emote));
            if ($front == substr($emote, 0, 3) and levenshtein($emote, $shortuname) <= 3) {
                throw new Exception ("Username too similar to an emote $prefix, try changing the first characters");
            }
        }

    }

    /**
     * @throws Exception
     */
    public function validateEmail(string $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception ('A valid email is required');
        }
        $emailDomain = strtolower(substr($email, strpos($email, '@') + 1));
        $blacklist = array_merge([], include _BASEDIR . '/config/domains.blacklist.php');
        if (in_array($emailDomain, $blacklist)) {
            throw new Exception ('email is blacklisted');
        }
    }

    /**
     * Starts up the session, looks for remember me if there was no session
     * Also updates the session if the user is flagged for it.
     * TODO this method is a mess
     *
     * @throws \Exception
     */
    public function startSession() {
        $redisService = ChatRedisService::instance();
        $userService = UserService::instance();

        // If the session has a cookie, start it
        if (Session::hasSessionCookie() && Session::start() && Session::hasRole(UserRole::USER)) {
            $sessionId = Session::getSessionId();
            if (!empty($sessionId)) {
                $redisService->renewChatSessionExpiration(Session::getSessionId());
            }
        }

        // Check the Remember me cookie if the session is invalid
        if (!Session::hasRole(UserRole::USER)) {
            $user = $this->getRememberMe();
            if (!empty($user)) {
                Session::start();
                $this->updateWebSession($user);
                $this->setRememberMe($user);
            }
        }

        // Update the user if they have been flagged for an update
        if (Session::hasRole(UserRole::USER)) {
            $creds = Session::getCredentials();
            $userId = $creds->getUserId();
            if (!empty($userId) && $this->isUserFlaggedForUpdate($userId)) {
                $user = $userService->getUserById($userId);
                $this->clearUserUpdateFlag($userId);
                $this->updateWebSession($user, $creds->getAuthProvider());
            }
        }
    }

    /**
     * @throws DBALException
     */
    public function buildUserCredentials(array $user, string $authProvider = ''): SessionCredentials {
        $userService = UserService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $creds = new SessionCredentials($user);
        $creds->setAuthProvider($authProvider);
        $creds->addRoles(UserRole::USER);
        $creds->addFeatures($userService->getFeaturesByUserId($user ['userId']));
        $creds->addRoles($userService->getRolesByUserId($user ['userId']));

        if ($user['istwitchsubscriber']) {
            $creds->addFeatures(UserFeature::SUBSCRIBER_TWITCH);
        }

        $sub = $subscriptionService->getUserActiveSubscription($user ['userId']);
        if (!empty ($sub)) {
            $creds->addRoles(UserRole::SUBSCRIBER);
            $creds->addFeatures(UserFeature::SUBSCRIBER);
            switch ($sub['subscriptionTier']) {
                case 1:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT1);
                    break;
                case 2:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT2);
                    break;
                case 3:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT3);
                    break;
                case 4:
                    $creds->addFeatures(UserFeature::SUBSCRIBERT4);
                    break;
            }
            $creds->setSubscription([
                'tier' => $sub['subscriptionTier'],
                'source' => $sub['subscriptionSource'],
                'type' => $sub['subscriptionType'],
                'start' => Date::getDateTime($sub['createdDate'])->format(Date::FORMAT),
                'end' => Date::getDateTime($sub['endDate'])->format(Date::FORMAT)
            ]);
        } else if ($user['istwitchsubscriber']) {
            $creds->addRoles(UserRole::SUBSCRIBER);
            $creds->addFeatures(UserFeature::SUBSCRIBER);
            $creds->setSubscription([
                'tier' => 1,
                'source' => 'twitch.tv',
                'type' => null,
                'start' => null,
                'end' => null
            ]);
        }
        return $creds;
    }

    /**
     * Generates a rememberme cookie
     * Note the rememberme cookie has a long expiry unlike the session cookie
     */
    public function setRememberMe(array $user) {
        try {
            $cookie = Session::instance()->getRememberMeCookie();
            $rawData = $cookie->getValue();
            if (!empty ($rawData)) {
                $cookie->clearCookie();
            }
            $expires = Date::getDateTime(time() + mt_rand(0, 2419200)); // 0-28 days
            $expires->add(new DateInterval('P1M'));
            $data = CryptoOpenSSL::encrypt(serialize([
                'userId' => $user['userId'],
                'expires' => $expires->getTimestamp()
            ]));
            $cookie->setValue($data, Date::getDateTime('NOW + 2 MONTHS')->getTimestamp());
        } catch (\Exception $e) {
            Log::error(new Exception('Failed to create remember me cookie.', $e));
        }
    }

    /**
     * Returns the user record associated with a remember me cookie
     * @return array|null
     *
     * @throws \Exception
     */
    protected function getRememberMe() {
        $cookie = Session::instance()->getRememberMeCookie();
        $rawData = $cookie->getValue();
        $user = null;
        if (empty($rawData))
            goto end;

        if (strlen($rawData) < 64)
            goto cleanup;

        $data = CryptoOpenSSL::decrypt($rawData);

        if (!$data)
            goto cleanup;

        $data = unserialize($data);
        if (!isset($data['expires']) or !isset($data['userId']))
            goto cleanup;

        $expires = Date::getDateTime($data['expires']);
        if ($expires <= Date::getDateTime())
            goto cleanup;

        $user = UserService::instance()->getUserById(intval($data['userId']));
        goto end;

        cleanup:
        $cookie->clearCookie();
        end:
        return $user;
    }

    /**
     * @throws Exception
     */
    public function updateWebSession(array $user, string $provider = '') {
        try {
            $credentials = $this->buildUserCredentials($user, $provider);
            //
            $session = Session::instance();
            foreach ($credentials->getData() as $name => $value) {
                $session->set($name, $value);
            }
            $session->setCredentials($credentials);
            //
            $redisService = ChatRedisService::instance();
            $sessionId = Session::getSessionId();
            if (boolval($user['allowChatting'])) {
                $redisService->setChatSession($credentials, $sessionId);
                $redisService->sendRefreshUser($credentials);
            } else {
                $redisService->removeChatSession($sessionId);
            }
        } catch (\Exception $e) {
            throw new Exception("Cannot update web session.", $e);
        }
    }

    public function removeWebSession() {
        if (Session::isStarted()) {
            $redis = ChatRedisService::instance();
            $redis->removeChatSession(Session::getSessionId());
            Session::destroy();
        }
    }

    /**
     * Flag a user session for update
     * So that on their next request, the session data is updated.
     * Also does a chat session refresh
     */
    public function flagUserForUpdate(int $userId) {
        try {
            $user = UserService::instance()->getUserById($userId);
            if (!empty($user)) {
                $creds = $this->buildUserCredentials($user);
                $this->setUserUpdateFlag($userId);
                $redisService = ChatRedisService::instance();
                $redisService->sendRefreshUser($creds);
            }
        } catch (\Exception $e) {
            Log::error("Error flagging user for update. {$e->getMessage()}");
        }
    }

    private function isUserFlaggedForUpdate(int $userId): bool {
        $cache = Application::getNsCache();
        $lastUpdated = $cache->fetch("refreshusersession-$userId");
        return !empty($lastUpdated);
    }

    private function clearUserUpdateFlag(int $userId) {
        $cache = Application::getNsCache();
        $cache->delete("refreshusersession-$userId");
    }

    private function setUserUpdateFlag(int $userId) {
        $cache = Application::getNsCache();
        $cache->save("refreshusersession-$userId", time(), intval(ini_get('session.gc_maxlifetime')));
    }

    private function getEmotesForValidation(): array {
        try {
            return EmoteService::instance()->findAllEmotes();
        } catch (DBALException $e) {
            Log::error("Emote failed to load. {$e->getMessage()}");
        }
        return [];
    }
}
