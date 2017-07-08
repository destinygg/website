<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Log;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Doctrine\DBAL\DBALException;

class AuthenticationRedirectionFilter {

    /**
     * @param AuthenticationCredentials $authCreds
     * @return string
     * @throws DBALException
     * @throws Exception
     */
    public function execute(AuthenticationCredentials $authCreds) {
        $authService = AuthenticationService::instance();
        $userService = UserService::instance();

        // Make sure the creds are valid
        if (!$authCreds->isValid()) {
            Log::error('Error validating auth credentials {creds}', ['creds' => var_export($authCreds, true)]);
            throw new Exception ('Invalid auth credentials');
        }

        $email = $authCreds->getEmail();
        if (!empty($email))
            $authService->validateEmail($authCreds->getEmail(), null, true);

        // Account merge
        if (Session::set('accountMerge') === '1') {
            // Must be logged in to do a merge
            if (!Session::hasRole(UserRole::USER)) {
                throw new Exception ('Authentication required for account merge');
            }
            $authService->handleAuthAndMerge($authCreds);
            return 'redirect: /profile/authentication';
        }

        // Follow url
        $follow = Session::set('follow');
        // Remember me checkbox on login form
        $rememberme = Session::set('rememberme');

        // If the user profile doesn't exist, go to the register page
        if (!$userService->getUserAuthProviderExists($authCreds->getAuthId(), $authCreds->getAuthProvider())) {
            Session::set('authSession', $authCreds);
            $url = '/register?code=' . urlencode($authCreds->getAuthCode());
            if (!empty($follow)) {
                $url .= '&follow=' . urlencode($follow);
            }
            return 'redirect: ' . $url;
        }

        // User exists, handle the auth
        $user = $authService->handleAuthCredentials($authCreds);
        try {
            if ($rememberme == true) {
                $authService->setRememberMe($user);
            }
        } catch (\Exception $e) {
            $n = new Exception('Failed to create remember me cookie.', $e);
            Log::error($n);
        }
        if (!empty ($follow) && substr($follow, 0, 1) == '/') {
            return 'redirect: ' . $follow;
        }
        return 'redirect: /profile';
    }
}