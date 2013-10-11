<?php
namespace Destiny\Twitch;

use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Exception;
use Destiny\Common\OAuthClient;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\ViewModel;

class TwitchAuthHandler {
	
	/**
	 * The current auth type
	 *
	 * @var string
	 */
	protected $authProvider = 'twitch';
	
	/**
	 * @param array $params        	
	 * @throws Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$authService = AuthenticationService::instance ();
		if (isset ( $params ['error'] ) && ! empty ( $params ['error'] )) {
			$model->title = 'Login error';
			$model->error = new Exception ( 'Authentication failed' );
			return 'login';
		}
		$authClient = new OAuthClient ( Config::$a ['oauth'] ['providers'] [$this->authProvider] );
		$authClient->setHeaderTokenName ( 'OAuth' );
		$accessToken = $authClient->fetchAccessToken ( $params ['code'], 'https://api.twitch.tv/kraken/oauth2/token', sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ) );
		$data = $authClient->fetchUserInfo ( $accessToken, 'https://api.twitch.tv/kraken/user' );
		$authCreds = $this->getAuthCredentials ( $params ['code'], $data );
		$authCredHandler = new AuthenticationRedirectionFilter ();
		return $authCredHandler->execute ( $authCreds );
	}
	
	/**
	 * Build a standard auth array from custom data array from api response
	 *
	 * @param string $code        	
	 * @param array $data        	
	 * @return AuthenticationCredentials
	 */
	private function getAuthCredentials($code, array $data) {
		if (empty ( $data ) || ! isset ( $data ['_id'] ) || empty ( $data ['_id'] )) {
			throw new Exception ( 'Authorization failed, invalid user data' );
		}
		$arr = array ();
		$arr ['authProvider'] = $this->authProvider;
		$arr ['authCode'] = $code;
		$arr ['authId'] = $data ['_id'];
		$arr ['authDetail'] = $data ['name'];
		$arr ['username'] = (isset ( $data ['display_name'] ) && ! empty ( $data ['display_name'] )) ? $data ['display_name'] : $data ['name'];
		$arr ['email'] = (isset ( $data ['email'] ) && ! empty ( $data ['email'] )) ? $data ['email'] : '';
		return new AuthenticationCredentials ( $arr );
	}
}