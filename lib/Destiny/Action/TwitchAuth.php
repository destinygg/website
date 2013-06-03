<?php
namespace Destiny\Action;

use Destiny\Application;
use Destiny\Session;
use Destiny\Config;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Logger;
use Destiny\Service\Fantasy\Db\User;
use Destiny\Service\Fantasy\Db\Team;
use Destiny\Utils\String\Params;

class TwitchAuth {

	public function execute(array $params) {
		$response = array ();
		$data = null;
		try {
			if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
				throw new \Exception ( 'Twitch authentication failed. Invalid or empty code.' );
			}
			// Since scope uses the + and running + through a url produces a space
			$scope = (isset ( $params ['scope'] )) ? str_replace ( ' ', '+', $params ['scope'] ) : null;
			if (empty ( $scope )) {
				throw new \Exception ( 'Twitch authentication failed. Scope empty or invalid.' );
			}
			
			$accessToken = $this->requestAuthToken ( $params ['code'] );
			if (empty ( $accessToken )) {
				throw new \Exception ( 'Twitch authentication token request failed.' );
			}
			
			$data = $this->requestUser ( $accessToken );
			if (empty ( $data )) {
				throw new \Exception ( 'Twitch authentication user request failed.' );
			}
			
			// Create a user array from the twitch response
			$user = $this->getUserFromData ( $data );
				
			// If the username is the broadcaster, and the permissions are NOT the same
			// the broadcaster tried to login, but we need additional permissions from that user.
			// So we redirect again, with the correct permissions
			$broadcaster = Config::$a ['twitch'] ['broadcaster'] ['user'];
			$broadcastPerms = Config::$a ['twitch'] ['broadcaster'] ['request_perms'];
			if (strcasecmp ( $user ['username'], $broadcaster ) === 0 && $scope != $broadcastPerms) {

				$log = new Logger ( Config::$a ['log'] ['path'] . 'error.log' );
				$log->log ( 'Requested broadcaster permissions ['. $broadcaster .']' );
				
				$url = 'https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=' . Config::$a ['twitch'] ['client_id'] . '&redirect_uri=' . urlencode ( Config::$a ['twitch'] ['redirect_uri'] ) . '&scope=' . Config::$a ['twitch'] ['broadcaster'] ['request_perms'];
				Http::header ( Http::HEADER_LOCATION, $url );
				exit;
			}
			
			User::getInstance ()->persistUser ( $user );
			Team::getInstance ()->setupUserTeam ( $user );
			
			Session::setAuthorized ( true );
			Session::setToken ( $accessToken );
			Session::setUserId ( $user ['userId'] );
			Session::persist ();
			
			Http::header ( Http::HEADER_LOCATION, '/league' );
			exit;
			
		} catch ( \Exception $e ) {
			Application::getInstance ()->error ( 500, $e );
		}

		Application::getInstance()->error(401);
	}
	
	private function getUserFromData($data) {
		$user = array ();
		$user ['admin'] = 0;
		$user ['externalId'] = $data->_id;
		$user ['username'] = $data->name;
		$user ['displayName'] = $data->display_name;
		$user ['email'] = $data->email;
		return $user;
	}
	
	private function requestAuthToken($code){
		$post = array (
			'code' => $code,
			'client_id' => Config::$a ['twitch'] ['client_id'],
			'client_secret' => Config::$a ['twitch'] ['client_secret'],
			'redirect_uri' => urlencode(Config::$a['twitch']['redirect_uri']),
			'grant_type' => 'authorization_code',
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
			throw new \Exception ( 'Request access token failed. ' . curl_error ( $curl ) );
		}
		$data = json_decode ( $response );
		if (! isset ( $data->access_token ) || empty ( $data->access_token )) {
			throw new \Exception ( 'Request access token failed. ' . $response );
		}
		return $data->access_token;
	}
	
	private function requestUser($token){
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/user?oauth_token=' . $token );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
		$data = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		if ($info ['http_code'] != 200) {
			throw new \Exception ( 'Request user failed. ' . curl_error ( $curl ) );
		}
		return json_decode ( $data );
	}

}