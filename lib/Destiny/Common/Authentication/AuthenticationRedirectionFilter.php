<?php
namespace Destiny\Common\Authentication;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserStatus;
use Destiny\Common\Utils\RandomString;
use Destiny\Discord\DiscordMessenger;

class AuthenticationRedirectionFilter {

    /**
     * @var OAuthResponse
     */
    private $authResponse;

    /**
     * @var Request
     */
    private $request;

    /**
     * @throws Exception
     */
    function __construct(OAuthResponse $authResponse, Request $request) {
        if (empty($authResponse) || !$authResponse->isValid()) {
            Log::error('Error validating auth response {creds}', ['creds' => var_export($authResponse, true)]);
            throw new Exception ('Invalid auth credentials');
        }
        $this->authResponse = $authResponse;
        $this->request = $request;
    }

    private function buildTempUsername(): string {
        return "tmp" . RandomString::makeUrlSafe(9);
    }

    /**
     * @throws Exception
     */
    public function execute(): string {
        $userService = UserService::instance();
        $userAuthService = UserAuthService::instance();
        $authService = AuthenticationService::instance();
        $authResponse = $this->authResponse;

        $isConnectingAccount = Session::getAndRemove('isConnectingAccount');
        $rememberme = Session::getAndRemove('rememberme');
        $follow = Session::getAndRemove('follow');
        $grant = Session::getAndRemove('grant');
        $uuid = Session::getAndRemove('uuid');

        if ($isConnectingAccount === '1') {
            if (!Session::hasRole(UserRole::USER)) {
                throw new Exception ('Authentication required for account merge');
            }
            if ($authService->validateAuthAccountDetails($authResponse)) {
                $userId = Session::getCredentials()->getUserId();
                $userAuthService->saveUserAuthWithOAuth($authResponse, $userId);
                Session::setSuccessBag('Profile connected!');
                return 'redirect: /profile/authentication';
            }
        }

        /**
         * If there is no existing user auth, validate/sanitize username and create a new user, with new auth
         * If there is a user auth, update the user auth
         */

        $user = null;
        $provider = $authResponse->getAuthProvider();
        $username = $authResponse->getUsername();
        $email = $authResponse->getAuthEmail();
        $authId = $authResponse->getAuthId();
        $userAuth = $userAuthService->getByAuthIdAndProvider($authId, $provider);

        if (empty($userAuth)) {
            if (!$authResponse->getVerified()) {
                throw new Exception (' You must have a verified email address for your registration to complete successfully.');
            }
            if (!empty($email)) {
                $authService->validateEmail($email);
            }

            $authService->validateAuthAccountDetails($authResponse);

            if (empty($username)) {
                $username = $this->buildTempUsername();
            } else {
                $username = $this->sanitizeUsername($username);
                try {
                    $authService->validateUsername($username);
                    $authService->checkUsernameForSimilarityToAllEmotes($username);
                    $userService->checkUsernameTaken($username);
                } catch (Exception $e) {
                    $username = $this->buildTempUsername();
                    Log::warn("Invalid username or username already taken '{$authResponse->getUsername()}''. Generating username '$username' for auth '$authId'. {$e->getMessage()}");
                }
            }
            $userId = $userService->addUser([
                'userStatus' => UserStatus::ACTIVE,
                'username' => $username,
                'allowNameChange' => 1,
                'allowChatting' => 0
            ]);

            DiscordMessenger::send('New account', [
                'fields' => [
                    ['title' => 'User', 'value' => DiscordMessenger::userLink($userId, $username), 'short' => false],
                    ['title' => 'Provider', 'value' => $authResponse->getAuthProvider(), 'short' => true],
                    ['title' => 'Username', 'value' => $authResponse->getUsername(), 'short' => true],
                    ['title' => 'Email', 'value' => $authResponse->getAuthEmail(), 'short' => true],
                ]
            ]);

            // Cache the IP address the new user signed up with.
            $r = $this->request;
            if (!empty($r->address())) {
                ChatRedisService::instance()->cacheIPForUser($userId, $r->address());
            }

            $user = $userService->getUserById($userId);
        } else {
            $userId = (int) $userAuth['userId'];
            $user = $userService->getUserById($userId);
        }
        $userAuthService->saveUserAuthWithOAuth($authResponse, $userId);
        //

        if (empty($user)) {
            Log::critical("User not found during redirection / login.");
            Session::setErrorBag("User not found");
            return 'redirect: /';
        }

        if ($user['userStatus'] != UserStatus::ACTIVE) {
            Log::debug("Inactive user attempted to login {$user['userId']}.");
            Session::setErrorBag("Invalid user status {$user['userStatus']}");
            return 'redirect: /';
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

            $oauthService = DggOAuthService::instance();
            $data = $oauthService->getFlashStore($uuid, 'uuid');
            $data['userId'] = $user['userId'];

            $code = RandomString::makeUrlSafe(64);
            $oauthService->saveFlashStore($code, $data);
            $oauthService->deleteFlashStore($uuid);

            $redirectUri = $data['redirect_uri'] . '?' . http_build_query(['code' => $code, 'state' => $data['state']], null, '&');
            return "redirect: $redirectUri";

        } else {

            // Renew the session upon successful login, makes it slightly harder to hijack
            $session = Session::instance();
            if ($session != null) {
                $session->renew();
            }
            $authService->updateWebSession($user, $provider);
            if ($rememberme) {
                $authService->setRememberMe($user);
            }
            if (boolval($user['allowNameChange'])) {
                return 'redirect: /profile';
            }
            Session::setSuccessBag('Login successful!');
            return (!empty($follow) && substr($follow, 0, 1) == '/') ? 'redirect: ' . $follow : 'redirect: /profile';
        }
    }

    /**
     * Convert a oauth provider username to a dgg compliant username
     * Some auth providers do not provide a username, in that case some other
     * value is used, which may contain illegal characters
     */
    public function sanitizeUsername(string $username): string {
        $username = preg_replace(AuthenticationService::REGEX_REPLACE_CHAR_USERNAME, '', $username);
        $length = mb_strlen($username);
        if ($length > AuthenticationService::USERNAME_MAX) {
            return mb_substr($username, 0, AuthenticationService::USERNAME_MAX);
        }
        if ($length < AuthenticationService::USERNAME_MIN) {
            return $username . RandomString::makeUrlSafe((AuthenticationService::USERNAME_MIN + 5) - $length);
        }
        return $username;
    }
}
