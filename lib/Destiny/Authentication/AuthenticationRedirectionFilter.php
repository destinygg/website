<?php
namespace Destiny\Authentication;

use Destiny\Authentication\AuthenticationCredentials;
use Destiny\Authentication\Service\AuthenticationService;
use Destiny\User\UserRole;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Session;

class AuthenticationRedirectionFilter {

	public function execute(AuthenticationCredentials $authCreds) {
		$authService = AuthenticationService::instance ();
		
		// Make sure the creds are valid
		if (! $authCreds->isValid ()) {
			Application::instance ()->getLogger ()->error ( sprintf ( 'Error validating auth credentials %s', var_export ( $authCreds, true ) ) );
			throw new Exception ( 'Invalid auth credentials' );
		}
		
		// Account merge
		if (Session::set ( 'accountMerge' ) === '1') {
			// Must be logged in to do a merge
			if (! Session::hasRole ( UserRole::USER )) {
				throw new Exception ( 'Authentication required' );
			}
			$authService->handleAuthAndMerge ( $authCreds );
			return 'redirect: /profile/authentication';
		}
		
		// If the user profile doesnt exist, go to the register page
		if (! $authService->getUserAuthProfileExists ( $authCreds )) {
			Session::set ( 'authSession', $authCreds );
			return 'redirect: /register?code=' . urlencode ( $authCreds->getAuthCode () );
		}
		
		// User exists, handle the auth
		$authService->handleAuthCredentials ( $authCreds );
		return 'redirect: /profile';
	}

}