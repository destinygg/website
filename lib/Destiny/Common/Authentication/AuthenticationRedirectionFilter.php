<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserRole;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Commerce\SubscriptionsService;

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

		// Follow url *notice the set, returning and clearing the var
		$follow = Session::set( 'follow' );

		// If the user profile doesnt exist, go to the register page
		if (! $authService->getUserAuthProfileExists ( $authCreds )) {
			Session::set ( 'authSession', $authCreds );
			$url = 'redirect: /register?code=' . urlencode ( $authCreds->getAuthCode () );
			if (! empty( $follow )) {
				$url .= '&follow=' . urlencode ( $follow);
			}
			return $url;
		}
		
		// User exists, handle the auth
		$authService->handleAuthCredentials ( $authCreds );
			
		// Check for an active "cart", redirect to cart page
		// @TODO clean this implementation up
		$selectSubscription = Session::get ( 'subscription' );
		if (! empty ( $selectSubscription )) {
			$currentSubscription = SubscriptionsService::instance ()->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
			if (empty ( $currentSubscription )) {
				return 'redirect: /subscription/confirm?subscription=' . $selectSubscription;
			}
			return 'redirect: /subscription/update/confirm?subscription=' . $selectSubscription;
		}
		
		if (! empty ( $follow ) and substr( $follow, 0, 1 ) == '/' ) {
			return 'redirect: ' . $follow;
		}
		return 'redirect: /profile';
	}
}