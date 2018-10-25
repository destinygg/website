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
            $authService->validateEmail($email, null, true);

        // Account merge
        if (Session::getAndRemove('accountMerge') === '1') {
            // Must be logged in to do a merge
            if (!Session::hasRole(UserRole::USER)) {
                throw new Exception ('Authentication required for account merge');
            }
            Session::setSuccessBag('Authorization successful!');
            $authService->handleAuthAndMerge($authCreds);
            return 'redirect: /profile';
        }

        $follow = Session::getAndRemove('follow');
        $rememberme = Session::getAndRemove('rememberme');

        // If the user profile doesn't exist, go to the register page
        if (!$userService->getUserAuthProviderExists($authCreds->getAuthId(), $authCreds->getAuthProvider())) {
            Session::set('authSession', $authCreds);
            $url = '/register?code=' . urlencode($authCreds->getAuthCode());
            if (!empty($follow)) {
                $url .= '&follow=' . urlencode($follow);
            }
            return "redirect: $url";
        }

        $user = $authService->handleAuthCredentials($authCreds);

        if ($rememberme) {
            try {
                $authService->setRememberMe($user);
            } catch (\Exception $e) {
                Log::error(new Exception('Failed to create remember me cookie.', $e));
            }
        }

        if (!empty ($follow) && substr($follow, 0, 1) == '/') {
            return "redirect: $follow";
        }
        return 'redirect: /profile';
    }
}