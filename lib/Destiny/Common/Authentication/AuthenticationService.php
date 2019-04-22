<?php
namespace Destiny\Common\Authentication;

use DateInterval;
use Destiny\Chat\EmoteService;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Utils\CryptoMcrypt;
use Destiny\Common\Utils\CryptoOpenSSL;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session\Session;
use Destiny\Common\Service;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Chat\ChatRedisService;
use Doctrine\DBAL\DBALException;

/**
 * @method static AuthenticationService instance()
 */
class AuthenticationService extends Service {

    /**
     * @param string $username
     * @throws Exception
     * @throws DBALException
     */
    public function validateUsername($username) {
        if (empty ($username))
            throw new Exception ('Username required');

        if (preg_match('/^[A-Za-z0-9_]{3,20}$/', $username) == 0)
            throw new Exception ('Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.');

        $emoteService = EmoteService::instance();
        $normalizeduname = strtolower($username);
        $front = substr($normalizeduname, 0, 2);

        // nick blacklists
        $blacklist = array_merge([], include _BASEDIR . '/config/nick.blacklist.php');
        if (in_array($normalizeduname, $blacklist)) {
            throw new Exception ('nick is blacklisted');
        }

        // nick-to-emote similarity heuristics, not perfect sadly ;(
        foreach (array_map(function($v) { return strtolower($v['prefix']); }, $emoteService->findAllEmotes()) as $normalizedemote) {
            if (strpos($normalizeduname, $normalizedemote) === 0) {
                throw new Exception ('Username too similar to an emote, try changing the first characters');
            }
            if ($normalizedemote == 'lul') { // TODO remove this static reference
                continue;
            }
            $shortuname = substr($normalizeduname, 0, strlen($normalizedemote));
            $emotefront = substr($normalizedemote, 0, 2);
            if ($front == $emotefront and levenshtein($normalizedemote, $shortuname) <= 2) {
                throw new Exception ('Username too similar to an emote, try changing the first characters');
            }
        }

        if (preg_match_all('/[0-9]{3}/', $username, $m) > 0)
            throw new Exception ('Too many numbers in a row in username');

        if (preg_match_all('/[\_]{2}/', $username, $m) > 0 || preg_match_all("/[_]+/", $username, $m) > 2)
            throw new Exception ('Too many underscores in username');

        if (preg_match_all("/[0-9]/", $username, $m) > round(strlen($username) / 2))
            throw new Exception ('Number ratio is too high in username');
    }

    /**
     * @param string $email
     * @throws Exception
     */
    public function validateEmail($email) {
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
                Session::updateCredentials($this->buildUserCredentials($user));
                $this->setRememberMe($user);

                // flagUserForUpdate updates the credentials AGAIN, but since its low impact
                // Instead of doing the logic in two places
                $this->flagUserForUpdate($user['userId']);
            }
        }

        // Update the user if they have been flagged for an update
        if (Session::hasRole(UserRole::USER)) {
            $userId = Session::getCredentials()->getUserId();
            if (!empty($userId) && $this->isUserFlaggedForUpdate($userId)) {
                $user = UserService::instance()->getUserById($userId);
                if (!empty ($user)) {
                    $this->clearUserUpdateFlag($userId);
                    Session::updateCredentials($this->buildUserCredentials($user));

                    // the refreshChatSession differs from this call, because only here we have access to the session id.
                    $redisService->setChatSession(Session::getCredentials(), Session::getSessionId());
                    $redisService->sendRefreshUser(Session::getCredentials());
                }
            }
        }
    }

    /**
     * @param array $user
     * @param string $authProvider
     * @return SessionCredentials
     * @throws DBALException
     */
    public function buildUserCredentials(array $user, $authProvider = null) {
        $userService = UserService::instance();
        $subscriptionService = SubscriptionsService::instance();
        $creds = new SessionCredentials ($user);
        $creds->setAuthProvider($authProvider);
        $creds->addRoles(UserRole::USER);
        $creds->addFeatures($userService->getFeaturesByUserId($user ['userId']));
        $creds->addRoles($userService->getRolesByUserId($user ['userId']));
        $sub = $subscriptionService->getUserActiveSubscription($user ['userId']);

        if ($user['istwitchsubscriber']) {
            $creds->addFeatures(UserFeature::SUBSCRIBER_TWITCH);
        }

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
     * Handles the authentication and then merging of accounts
     * Merging of an account is basically connecting multiple authenticators to one user
     *
     * @param AuthenticationCredentials $authCreds
     * @throws DBALException
     */
    public function handleAuthAndMerge(AuthenticationCredentials $authCreds) {
        $userService = UserService::instance();
        $user = Session::getCredentials()->getData();

        // If this auth profile exists, delete it what ever user had it.
        $existingAuth = $userService->getAuthByIdAndProvider($authCreds->getAuthId(), $authCreds->getAuthProvider());
        if (!empty($existingAuth)) {
            $userService->removeAuthProfile($existingAuth['userId'], $authCreds->getAuthProvider());
        }

        // Add the auth profile to the user
        $userService->addUserAuthProfile([
            'userId' => $user['userId'],
            'authProvider' => $authCreds->getAuthProvider(),
            'authId' => $authCreds->getAuthId(),
            'authCode' => $authCreds->getAuthCode(),
            'authDetail' => $authCreds->getAuthDetail(),
            'refreshToken' => $authCreds->getRefreshToken()
        ]);
    }

    /**
     * Generates a rememberme cookie
     * Note the rememberme cookie has a long expiry unlike the session cookie
     *
     * @param array $user
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
     * @return array
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

        // TODO Remove CryptoMcrypt::decrypt in 30 days from deployment
        $data = CryptoOpenSSL::decrypt($rawData);
        if (empty($data)) {
            $data = CryptoMcrypt::decrypt($rawData);
        }

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
     * Flag a user session for update
     * So that on their next request, the session data is updated.
     * Also does a chat session refresh
     *
     * @param array|number $user
     * @throws DBALException
     */
    public function flagUserForUpdate($user) {
        if (!is_array($user))
            $user = UserService::instance()->getUserById($user);
        if (!empty($user)) {
            $cache = Application::getNsCache();
            $cache->save('refreshusersession-' . $user['userId'], time(), intval(ini_get('session.gc_maxlifetime')));
            $redisService = ChatRedisService::instance();
            $redisService->sendRefreshUser($this->buildUserCredentials($user));
        }
    }

    /**
     * @param $userId
     */
    protected function clearUserUpdateFlag($userId) {
        $cache = Application::getNsCache();
        $cache->delete('refreshusersession-' . $userId);
    }

    /**
     * @param int $userId
     * @return bool
     */
    protected function isUserFlaggedForUpdate($userId) {
        $cache = Application::getNsCache();
        $lastUpdated = $cache->fetch('refreshusersession-' . $userId);
        return !empty ($lastUpdated);
    }

}