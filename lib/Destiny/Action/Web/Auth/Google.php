<?php
namespace Destiny\Action\Web\Auth;

use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Config;
use Destiny\Common\OAuthClient;
use Destiny\Common\Security\AuthenticationRedirectionFilter;
use Destiny\Common\Security\AuthenticationCredentials;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Google {
	
	/**
	 * The current auth type
	 *
	 * @var string
	 */
	protected $authProvider = 'google';

	/**
	 * @Route ("/auth/google")
	 * @Transactional
	 *
	 * Handle the incoming oAuth request
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$UserService = UserService::instance ();
		$authService = AuthenticationService::instance ();
		try {
			if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
				throw new Exception ( 'Authentication failed, invalid or empty code.' );
			}
			$authClient = new OAuthClient ( Config::$a ['oauth'] ['providers'] [$this->authProvider] );
			$authClient->setHeaderTokenName ( 'Bearer' );
			$accessToken = $authClient->fetchAccessToken ( $params ['code'], 'https://accounts.google.com/o/oauth2/token', sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ) );
			$data = $authClient->fetchUserInfo ( $accessToken, 'https://www.googleapis.com/oauth2/v2/userinfo' );
			$authCreds = $this->getAuthCredentials ( $params ['code'], $data );
			$authCredHandler = new AuthenticationRedirectionFilter ();
			return $authCredHandler->execute ( $authCreds );
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
	 * @return AuthenticationCredentials
	 */
	private function getAuthCredentials($code, array $data) {
		if (empty ( $data ) || ! isset ( $data ['id'] ) || empty ( $data ['id'] )) {
			throw new Exception ( 'Authorization failed, invalid user data' );
		}
		$arr = array ();
		$arr ['authProvider'] = $this->authProvider;
		$arr ['authCode'] = $code;
		$arr ['authId'] = $data ['id'];
		$arr ['authDetail'] = $data ['email'];
		$arr ['username'] = (isset ( $data ['hd'] )) ? $data ['hd'] : '';
		$arr ['email'] = $data ['email'];
		return new AuthenticationCredentials ( $arr );
	}

}