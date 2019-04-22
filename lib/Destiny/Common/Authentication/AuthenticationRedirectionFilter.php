<?php
namespace Destiny\Common\Authentication;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Log;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\RandomString;
use Doctrine\DBAL\DBALException;

class AuthenticationRedirectionFilter {

    /**
     * @var AuthenticationCredentials
     */
    private $creds;

    /**
     * @param AuthenticationCredentials $authCreds
     * @throws Exception
     */
    function __construct(AuthenticationCredentials $authCreds) {
        if (empty($authCreds) || !$authCreds->isValid()) {
            Log::error('Error validating auth credentials {creds}', ['creds' => var_export($authCreds, true)]);
            throw new Exception ('Invalid auth credentials');
        }
        $this->creds = $authCreds;
    }

    /**
     * @return string
     * @throws DBALException
     * @throws Exception
     */
    public function execute() {
        if (!$this->creds) {
            throw new Exception('Invalid authentication credentials');
        }

        $authService = AuthenticationService::instance();
        $userService = UserService::instance();

        $isConnectingAccount = Session::getAndRemove('isConnectingAccount');
        $rememberme = Session::getAndRemove('rememberme');
        $follow = Session::getAndRemove('follow');
        $grant = Session::getAndRemove('grant');
        $uuid = Session::getAndRemove('uuid');

        // Connecting account
        if ($isConnectingAccount === '1') {
            // Must be logged in to do a merge
            if (!Session::hasRole(UserRole::USER)) {
                throw new Exception ('Authentication required for account merge');
            }
            Session::setSuccessBag('Authorization successful!');
            $authService->handleAuthAndMerge($this->creds);
            return 'redirect: /profile';
        }

        // If the user profile doesn't exist, go to the register page
        if (!$userService->getAuthExistsByAuthIdAndProvider($this->creds->getAuthId(), $this->creds->getAuthProvider())) {
            Session::set(Session::KEY_AUTH_SESSION, $this->creds);
            $url = '/register';
            $url .= '?code=' . urlencode($this->creds->getAuthCode());
            if (!empty($follow)) {
                $url .= '&follow=' . urlencode($follow);
            }
            if (!empty($rememberme)) {
                $url .= '&rememberme=' . ($rememberme ? 1 : 0);
            }
            if (!empty($grant)) {
                $url .= '&grant=' . urlencode($grant);
            }
            if (!empty($uuid)) {
                $url .= '&uuid=' . urlencode($uuid);
            }
            return "redirect: $url";
        }
        // We return to this point, after /register

        // At this point the user should exist
        $user = $userService->getAuthByIdAndProvider($this->creds->getAuthId(), $this->creds->getAuthProvider());
        if (empty ($user)) {
            throw new Exception ('Invalid auth user');
        }

        // Deleted user aren't allowed to login
        if ($user['userStatus'] === 'Deleted') {
            Session::setErrorBag('This user has been marked for deletion');
            return 'redirect: /';
        }

        // Update the auth profile for this provider
        $authProfile = $userService->getAuthByUserAndProvider($user['userId'], $this->creds->getAuthProvider());
        if (!empty ($authProfile)) {
            $userService->updateUserAuthProfile($user['userId'], $this->creds->getAuthProvider(), [
                'authCode' => $this->creds->getAuthCode(),
                'authDetail' => $this->creds->getAuthDetail(),
                'authEmail' => $this->creds->getAuthEmail(),
            ]);
        }

        /**
         * Response is different depending on the grant parameter.
         * If the grant is 'code' the user is requesting an access token
         * else this is a normal web login
         */
        if ($grant === 'code') {

            // TODO encapsulate this better
            if (empty($uuid)) {
                throw new Exception('Required uuid code');
            }

            $oauthService = OAuthService::instance();
            $data = $oauthService->getFlashStore($uuid, 'uuid');
            $data['userId'] = $user['userId'];

            $code = RandomString::makeUrlSafe(64);
            $oauthService->saveFlashStore($code, $data);
            $oauthService->deleteFlashStore($uuid);

            $redirectUri = $data['redirect_uri'];
            $redirectUri .= '?code=' . urlencode($code);
            $redirectUri .= '&state=' . urlencode($data['state']);
            return "redirect: $redirectUri";

        } else {

            // Renew the session upon successful login, makes it slightly harder to hijack
            $session = Session::instance();
            $session->renew(true);

            $credentials = $authService->buildUserCredentials($user, $this->creds->getAuthProvider());
            Session::updateCredentials($credentials);

            $redisService = ChatRedisService::instance();
            $redisService->setChatSession($credentials, Session::getSessionId());
            $redisService->sendRefreshUser($credentials);

            // Issue the web login flow
            if ($rememberme) {
                $authService->setRememberMe($user);
            }

            // Login success, redirect to /profile, or the follow url if its RELATIVE
            if (!empty($follow) && substr($follow, 0, 1) == '/') {
                return "redirect: $follow";
            } else {
                Session::setSuccessBag('Login successful!');
                return 'redirect: /profile';
            }
        }
    }

}