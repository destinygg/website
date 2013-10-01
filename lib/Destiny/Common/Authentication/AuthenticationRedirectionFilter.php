<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserRole;
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
				throw new Exception ( 'Authentication required for account merge' );
			}
			$authService->handleAuthAndMerge ( $authCreds );
			return 'redirect: /profile/authentication';
		}

		// Follow url *notice the set, clearing the var
		$follow = Session::set( 'follow' );
		
		// If the user profile doesnt exist, go to the register page
		if (! $authService->getUserAuthProfileExists ( $authCreds )) {
			Session::set ( 'authSession', $authCreds );
			$url = 'redirect: /register?code=' . urlencode ( $authCreds->getAuthCode () );
			if (! empty ( $follow )) {
				$url .= '&follow=' . urlencode ( $follow );
			}
			return $url;
		}
		
		// User exists, handle the auth
		$authService->handleAuthCredentials ( $authCreds );
		if (! empty ( $follow )) {
			return 'redirect: /' . $follow;
		}
		return 'redirect: /profile';
	}
}