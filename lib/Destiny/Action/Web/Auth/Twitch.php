<?php
namespace Destiny\Action\Web\Auth;

use Destiny\Common\ViewModel;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Application;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Logger;
use Destiny\Common\Service\UserService;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Utils\String\Params;
use Destiny\Common\Exception;
use Destiny\Common\OAuthClient;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Twitch {
	
	/**
	 * The current auth type
	 *
	 * @var string
	 */
	protected $authProvider = 'twitch';

	/**
	 * @Route ("/auth/twitch")
	 *
	 * Handle the incoming oAuth request
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$authService = AuthenticationService::instance ();
		try {
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
			
			if (Session::get ( 'accountMerge' ) === '1') {
				$authService->handleAuthAndMerge ( $authCreds );
				return 'redirect: /profile/authentication';
			} else {
				$authService->handleAuthCredentials ( $authCreds );
				return 'redirect: /profile';
			}
		} catch ( Exception $e ) {
			$model->title = 'Login error';
			$model->error = $e;
			return 'login';
		}
	}

	/**
	 * Build a standard auth array from custom data array from api response
	 *
	 * @param string $code
	 * @param array $data
	 * @return array
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
		return $arr;
	}

}