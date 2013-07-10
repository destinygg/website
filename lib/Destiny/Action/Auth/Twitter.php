<?php

namespace Destiny\Action\Auth;

use Destiny\ViewModel;
use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\Session;
use Destiny\Config;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Logger;
use Destiny\Service\UserService;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Utils\String\Params;
use Destiny\AppException;
use Destiny\OAuthClient;

class Twitter {
	
	/**
	 * The current auth type
	 *
	 * @var string
	 */
	protected $authProvider = 'twitter';

	/**
	 * Build a standard auth array from custom data array from api response
	 *
	 * @param string $code
	 * @param array $data
	 * @return array
	 */
	private function getAuthCredentials($code, array $data) {
		if (empty ( $data ) || ! isset ( $data ['user_id'] ) || empty ( $data ['user_id'] )) {
			throw new AppException ( 'Authorization failed, invalid user data' );
		}
		$arr = array ();
		$arr ['authProvider'] = $this->authProvider;
		$arr ['authCode'] = $code;
		$arr ['authId'] = $data ['user_id'];
		$arr ['authDetail'] = $data ['screen_name'];
		$arr ['username'] = $data ['screen_name'];
		$arr ['email'] = '';
		return $arr;
	}

	/**
	 * Handle the incoming oAuth request
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function execute(array $params, ViewModel $model) {
		$UserService = UserService::instance ();
		$authService = AuthenticationService::instance ();
		try {
			if ((! isset ( $params ['oauth_token'] ) || empty ( $params ['oauth_token'] )) || ! isset ( $params ['oauth_verifier'] ) || empty ( $params ['oauth_verifier'] )) {
				throw new AppException ( 'Authentication failed' );
			}
			$oauth = Session::set ( 'oauth' );
			if ($params ['oauth_token'] !== $oauth ['oauth_token']) {
				throw new AppException ( 'Invalid login session' );
			}
			
			$twitterOAuthConf = Config::$a ['oauth'] ['providers'] ['twitter'];
			$tmhOAuth = new \tmhOAuth ( array (
					'consumer_key' => $twitterOAuthConf ['clientId'],
					'consumer_secret' => $twitterOAuthConf ['clientSecret'],
					'token' => $oauth ['oauth_token'],
					'secret' => $oauth ['oauth_token_secret'],
					'curl_connecttimeout' => Config::$a ['curl'] ['connecttimeout'],
					'curl_timeout' => Config::$a ['curl'] ['timeout'],
					'curl_ssl_verifypeer' => Config::$a ['curl'] ['verifypeer'] 
			) );
			$code = $tmhOAuth->user_request ( array (
					'method' => 'POST',
					'url' => $tmhOAuth->url ( 'oauth/access_token', '' ),
					'params' => array (
							'oauth_verifier' => trim ( $params ['oauth_verifier'] ) 
					) 
			) );
			if ($code != 200) {
				throw new AppException ( 'Failed to retrieve user data' );
			}
			$data = $tmhOAuth->extract_params ( $tmhOAuth->response ['response'] );
			$authCreds = $this->getAuthCredentials ( $oauth ['oauth_token'], $data );
			$authService->handleAuthCredentials ( $authCreds );
		} catch ( AppException $e ) {
			$model->title = 'Login error';
			$model->error = $e;
			return 'login';
		}
	}

}