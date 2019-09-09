<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserStatus;
use Destiny\Common\Utils\RandomString;
use Destiny\Discord\DiscordMessenger;
use Doctrine\DBAL\DBALException;

class AuthenticationRedirectionFilter {

    /**
     * @var OAuthResponse
     */
    private $authResponse;

    /**
     * @throws Exception
     */
    function __construct(OAuthResponse $authResponse) {
        if (empty($authResponse) || !$authResponse->isValid()) {
            Log::error('Error validating auth response {creds}', ['creds' => var_export($authResponse, true)]);
            throw new Exception ('Invalid auth credentials');
        }
        $this->authResponse = $authResponse;
    }

    private function buildTempUsername(): string {
        return "tmp" . RandomString::makeUrlSafe(9);
    }

    /**
     * @throws DBALException
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
            $userId = Session::getCredentials()->getUserId();
            $userAuthService->saveUserAuthWithOAuth($authResponse, $userId);
            Session::setSuccessBag('Profile connected!');
            return 'redirect: /profile/authentication';
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
            if (empty($username)) {
                $username = $this->buildTempUsername();
            } else {
                $username = $this->sanitizeUsername($username);
                try {
                    $authService->validateUsername($username);
                    $userService->checkUsernameTaken($username);
                } catch (Exception $e) {
                    $username = $this->buildTempUsername();
                    Log::warn("Invalid username or username already taken '{$authResponse->getUsername()}''. Generating username '$username' for auth '$authId'. {$e->getMessage()}");
                }
            }
            $userId = $userService->addUser([
                'username' => $username,
                'allowChatting' => 0,
                'allowNameChange' => 1,
                'userStatus' => UserStatus::ACTIVE,
            ]);

            $messenger = DiscordMessenger::instance();
            $messenger->send("{user} created a new account.", [['fields' => [
                ['title' => 'Provider', 'value' => $authResponse->getAuthProvider(), 'short' => true],
                ['title' => 'Username', 'value' => $authResponse->getUsername(), 'short' => true],
            ]]], ['userId' => $userId, 'username' => $username]);

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
            Session::instance()->renew();
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