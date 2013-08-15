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
use Destiny\Common\AppException;
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
	 * @throws AppException
	 */
	public function execute(array $params, ViewModel $model) {
		$authService = AuthenticationService::instance ();
		try {
			if (isset ( $params ['error'] ) && ! empty ( $params ['error'] )) {
				$model->title = 'Login error';
				$model->error = new AppException ( 'Authentication failed' );
				return 'login';
			}
			$authClient = new OAuthClient ( Config::$a ['oauth'] ['providers'] [$this->authProvider] );
			$authClient->setHeaderTokenName ( 'OAuth' );
			$accessToken = $authClient->fetchAccessToken ( $params ['code'], 'https://api.twitch.tv/kraken/oauth2/token', sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ) );
			$data = $authClient->fetchUserInfo ( $accessToken, 'https://api.twitch.tv/kraken/user' );
			$authCreds = $this->getAuthCredentials ( $params ['code'], $data );
			
			// Weird twitch broadcaster quirk
			$broadcaster = Config::$a ['twitch'] ['broadcaster'] ['user'];
			if (! empty ( $broadcaster ) && Config::$a ['twitch'] ['broadcasterAuth'] == true && strcasecmp ( $authCreds ['username'], $broadcaster ) === 0) {
				$this->handleBroadcasterLogin ( $authClient, $accessToken, $params );
			}
			if (Session::get ( 'accountMerge' ) == 1) {
				$authService->handleAuthAndMerge ( $authCreds );
			} else {
				$authService->handleAuthCredentials ( $authCreds );
			}
		} catch ( AppException $e ) {
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
			throw new AppException ( 'Authorization failed, invalid user data' );
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

	/**
	 * Because twitch / subscriber calls are only authed against the broadcaster, we need to do some magic when they try to login
	 *
	 * @param oAuthClient $authClient
	 * @param string $accessToken
	 * @param array $params
	 * @throws AppException
	 */
	protected function handleBroadcasterLogin(OAuthClient $authClient, $accessToken, array $params) {
		// If the username is the broadcaster, and the permissions are NOT the same
		// the broadcaster tried to login, but we need additional permissions from that user.
		// So we redirect again, with the correct permissions
		$broadcaster = Config::$a ['twitch'] ['broadcaster'] ['user'];
		$broadcastPerms = 'channel_check_subscription+channel_subscriptions+user_read';
		// Since scope uses the + and running + through a url produces a space
		$scope = (isset ( $params ['scope'] )) ? str_replace ( ' ', '+', $params ['scope'] ) : null;
		if (! empty ( $scope )) {
			if ($scope != $broadcastPerms) {
				$log = Application::instance ()->getLogger ();
				$log->notice ( 'Requested broadcaster permissions [' . $broadcaster . ']' );
				$authClient->sendAuthorisation ( 'https://api.twitch.tv/kraken/oauth2/authorize', sprintf ( Config::$a ['oauth'] ['callback'], $this->authProvider ), $broadcastPerms );
				exit ();
			}
			file_put_contents ( Config::$a ['cache'] ['path'] . 'BROADCASTERTOKEN.tmp', $accessToken );
			// end the broadcaster strangeness
		}
	}

}