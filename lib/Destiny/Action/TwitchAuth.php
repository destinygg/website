<?php

namespace Destiny\Action;

use Destiny\SessionAuthenticationCredentials;
use Destiny\Application;
use Destiny\Session;
use Destiny\Config;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Logger;
use Destiny\Service\UsersService;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Service\SubscriptionsService;
use Destiny\Service\Settings;
use Destiny\Utils\String\Params;
use Destiny\AppException;

class TwitchAuth {

	public function execute(array $params) {
		$response = array ();
		$data = null;
		if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
			throw new AppException ( 'Twitch authentication failed. Invalid or empty code.' );
		}
		// Since scope uses the + and running + through a url produces a space
		$scope = (isset ( $params ['scope'] )) ? str_replace ( ' ', '+', $params ['scope'] ) : null;
		if (empty ( $scope )) {
			throw new AppException ( 'Twitch authentication failed. Scope empty or invalid.' );
		}
		
		$accessToken = $this->requestAuthToken ( $params ['code'] );
		if (empty ( $accessToken )) {
			throw new AppException ( 'Twitch authentication token request failed.' );
		}
		
		$data = $this->requestUser ( $accessToken );
		if (empty ( $data )) {
			throw new AppException ( 'Twitch authentication user request failed.' );
		}
		
		// Create a user array from the twitch response
		$user = $this->getUserFromData ( $data );
		
		// If the username is the broadcaster, and the permissions are NOT the same
		// the broadcaster tried to login, but we need additional permissions from that user.
		// So we redirect again, with the correct permissions
		$broadcaster = Config::$a ['twitch'] ['broadcaster'] ['user'];
		$broadcastPerms = Config::$a ['twitch'] ['broadcaster'] ['request_perms'];
		if (strcasecmp ( $user ['username'], $broadcaster ) === 0 && $scope != $broadcastPerms) {
			$log = Application::instance ()->getLogger ();
			$log->notice ( 'Requested broadcaster permissions ['. $broadcaster . ']' );
			Http::header ( Http::HEADER_LOCATION, 'https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=' . Config::$a ['twitch'] ['client_id'] . '&redirect_uri=' . urlencode ( Config::$a ['twitch'] ['redirect_uri'] ) . '&scope=' . Config::$a ['twitch'] ['broadcaster'] ['request_perms'] );
			exit ();
		}
		
		$teamsService = TeamService::instance ();
		$usersService = UsersService::instance ();
		$subsService = SubscriptionsService::instance ();
		// See if there is already a user with the TwitchId as the externalId
		$existingUser = $usersService->getUserByExternalId ( $user ['externalId'] );
		if (! empty ( $existingUser )) {
			// Since someone might change their user via twitch we update after each auth
			$existingUser ['username'] = $user ['username'];
			$existingUser ['displayName'] = $user ['displayName'];
			$existingUser ['email'] = $user ['email'];
			$user = $usersService->updateUser ( $existingUser );
		} else {
			// If not user already exists, add the new one
			$user = $usersService->addUser ( $user );
		}
		
		// We should have a user with a ID by now
		if (empty ( $user ['userId'] )) {
			throw new AppException ( 'Invalid userId' );
		}
		
		// If this user has no team, create a new one
		$team = $teamsService->getTeamByUserId ( $user ['userId'] );
		if (empty ( $team )) {
			$team = $teamsService->addTeam ( $user ['userId'], Config::$a ['fantasy'] ['team'] ['startCredit'], Config::$a ['fantasy'] ['team'] ['startTransfers'] );
		}
		// This variable is important to set, but we dont have much error checking
		Session::set ( 'teamId', $team ['teamId'] );
		
		// Get the users active subscriptions
		$subscription = $subsService->getUserActiveSubscription ( $user ['userId'] );
		
		// Complete full authentication
		$authCreds = new SessionAuthenticationCredentials ();
		$authCreds->setUserId ( $user ['userId'] );
		$authCreds->setUserName ( $user ['username'] );
		$authCreds->setEmail ( $user ['email'] );
		$authCreds->setDisplayName ( $user ['displayName'] );
		$authCreds->setCountry ( $user ['country'] );
		$authCreds->setAuthorized ( true );
		$authCreds->addRoles ( 'user' );
		if (! empty ( $subscription )) {
			$authCreds->addRoles ( 'subscriber' );
		}
		if (isset ( $user ['admin'] ) && $user ['admin'] == '1') {
			$authCreds->addRoles ( 'admin' );
		}
		Session::setAuthCreds ( $authCreds );
		
		// Setup user preferences - must be done after the session has been created
		$settingsService = Settings::instance ();
		$settings = $settingsService->getUserSettings ( $user ['userId'] );
		$settingsService->setSettings ( $settings );
		
		// Redirect to... league page.. weird
		Http::header ( Http::HEADER_LOCATION, '/league' );
		exit ();
	}

	/**
	 * Return a standar user array from the data return from twitch
	 *
	 * @param object $data
	 * @return array
	 */
	private function getUserFromData(array $data) {
		$user = array ();
		$user ['externalId'] = $data ['_id'];
		$user ['username'] = $data ['name'];
		$user ['displayName'] = $data ['display_name'];
		$user ['email'] = $data ['email'];
		return $user;
	}

	/**
	 * Request a auth token from twitch
	 *
	 * @param string $code
	 * @return string accessToken
	 */
	private function requestAuthToken($code) {
		$post = array (
				'code' => $code,
				'client_id' => Config::$a ['twitch'] ['client_id'],
				'client_secret' => Config::$a ['twitch'] ['client_secret'],
				'redirect_uri' => urlencode ( Config::$a ['twitch'] ['redirect_uri'] ),
				'grant_type' => 'authorization_code' 
		);
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/oauth2/token' );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, Params::params ( $post ) );
		$response = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		if ($info ['http_code'] != 200) {
			throw new AppException ( 'Request access token failed.' );
		}
		$data = json_decode ( $response, true );
		if (! isset ( $data ['access_token'] ) || empty ( $data ['access_token'] )) {
			throw new AppException ( 'Request access token failed.' );
		}
		return $data ['access_token'];
	}

	/**
	 * Request a user from the API
	 *
	 * @param string $token
	 * @return array
	 */
	private function requestUser($token) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/user?oauth_token=' . $token );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		$data = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		if ($info ['http_code'] != 200) {
			throw new AppException ( 'Request user failed.' );
		}
		return json_decode ( $data, true );
	}

}