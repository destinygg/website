<?php

namespace Destiny\Action;

use Destiny\AuthenticationManager;
use Destiny\Application;
use Destiny\Session;
use Destiny\Config;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Logger;
use Destiny\Service\UsersService;
use Destiny\Service\Fantasy\TeamService;
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
		
		// If the username is the broadcaster, and the permissions are NOT the same
		// the broadcaster tried to login, but we need additional permissions from that user.
		// So we redirect again, with the correct permissions
		$broadcaster = Config::$a ['twitch'] ['broadcaster'] ['user'];
		$broadcastPerms = Config::$a ['twitch'] ['broadcaster'] ['request_perms'];
		
		if (strcasecmp ( $data ['name'], $broadcaster ) === 0) {
			if ($scope != $broadcastPerms) {
				$log = Application::instance ()->getLogger ();
				$log->notice ( 'Requested broadcaster permissions [' . $broadcaster . ']' );
				Http::header ( Http::HEADER_LOCATION, 'https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=' . Config::$a ['twitch'] ['client_id'] . '&redirect_uri=' . urlencode ( Config::$a ['twitch'] ['redirect_uri'] ) . '&scope=' . Config::$a ['twitch'] ['broadcaster'] ['request_perms'] );
				exit ();
			}
			$fp = fopen ( Config::$a ['cache'] ['path'] . 'BROADCASTERTOKEN.tmp', 'w' );
			fwrite ( $fp, $accessToken );
			fclose ( $fp );
		}
		
		$usersService = UsersService::instance ();
		// See if there is already a user with the TwitchId as the externalId
		$user = $usersService->getUserByExternalId ( $data ['_id'] );
		if (! empty ( $user )) {
			// Since someone might change their user via twitch we update after each auth
			if ($user ['displayName'] != $data ['display_name'] || $user ['email'] != $data ['email']) {
				$user ['displayName'] = $data ['display_name'];
				$user ['email'] = $data ['email'];
				$usersService->updateUser ( $user );
			}
		} else {
			// Create a user from the twitch response
			$user = array ();
			$user ['externalId'] = $data ['_id'];
			$user ['username'] = $data ['name'];
			$user ['displayName'] = $data ['display_name'];
			$user ['email'] = $data ['email'];
			$user ['country'] = '';
			$user ['userId'] = $usersService->addUser ( $user );
		}
		
		// We should have a user with a ID by now
		if (empty ( $user ['userId'] )) {
			throw new AppException ( 'Invalid userId' );
		}
		
		// Setup the users session
		$authManager = AuthenticationManager::instance ();
		$authManager->login ( $user );
		
		// Redirect to... league page.. weird!
		Http::header ( Http::HEADER_LOCATION, '/league' );
		exit ();
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