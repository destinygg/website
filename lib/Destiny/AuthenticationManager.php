<?php

namespace Destiny;

use Destiny\Session;
use Destiny\Service;
use Destiny\Service\UsersService;
use Destiny\Service\SubscriptionsService;
use Destiny\Service\Settings;
use Destiny\Service\Fantasy\TeamService;

class AuthenticationManager extends Service {
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return Authenticationmanager
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Setup the authenticated user
	 *
	 * @param array $user
	 */
	public function login(array $user) {
		$session = Session::instance ();
		
		$authCreds = new SessionAuthenticationCredentials ();
		$authCreds->setUserId ( $user ['userId'] );
		$authCreds->setUserName ( $user ['username'] );
		$authCreds->setEmail ( $user ['email'] );
		$authCreds->setDisplayName ( $user ['displayName'] );
		$authCreds->setCountry ( $user ['country'] );
		$authCreds->addRoles ( 'user' );
		
		// Get the users active subscriptions
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
		if (! empty ( $subscription )) {
			$authCreds->addRoles ( 'subscriber' );
		}
		
		// Get the stored roles
		$authCreds->addRoles ( UsersService::instance ()->getUserRoles ( $user ['userId'] ) );
		
		// Update the auth credentials
		Session::updateAuthCreds ( $authCreds );
		
		// Setup user preferences
		$settingsService = Settings::instance ();
		$settingsService->setSettings ( $settingsService->getUserSettings ( $user ['userId'] ) );
		
		// @TODO find a better place for this
		// If this user has no team, create a new one
		$teamId = Session::get ( 'teamId' );
		if (empty ( $teamId )) {
			$team = TeamService::instance ()->getTeamByUserId ( $user ['userId'] );
			if (empty ( $team )) {
				TeamService::instance ()->addTeam ( $user ['userId'], Config::$a ['fantasy'] ['team'] ['startCredit'], Config::$a ['fantasy'] ['team'] ['startTransfers'] );
			}
			Session::set ( 'teamId', $team ['teamId'] );
		}
	}

}