<?php
namespace Destiny\Action\Web;

use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\AppException;
use Destiny\Common\Config;
use Destiny\Common\OAuthClient;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Register {

	/**
	 * Make sure we have a valid session
	 *
	 * @param array $params
	 * @throws AppException
	 * @return array
	 */
	private function getAuthSession(array $params) {
		if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
			throw new AppException ( 'Invalid code' );
		}
		$authSession = Session::get ( 'authSession' );
		if (empty ( $authSession ) || empty ( $authSession ['authCode'] ) || ($params ['code'] != $authSession ['authCode'])) {
			throw new AppException ( 'Invalid authentication code' );
		}
		if (empty ( $authSession ['authProvider'] ) || empty ( $authSession ['authCode'] ) || empty ( $authSession ['authId'] )) {
			throw new AppException ( 'Invalid authentication information' );
		}
		return $authSession;
	}

	/**
	 * @Route ("/register")
	 * @HttpMethod ({"GET"})
	 *
	 * Handle the confirmation request
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function executeGet(array $params, ViewModel $model) {
		$authSession = $this->getAuthSession ( $params );
		$model->title = 'New user';
		$model->username = $authSession ['username'];
		
		if (! empty ( $authSession ['username'] ) && empty ( $authSession ['email'] )) {
			$authSession ['email'] = $authSession ['username'] . '@destiny.gg';
		}
		
		$model->email = $authSession ['email'];
		$model->authProvider = $authSession ['authProvider'];
		$model->code = $authSession ['authCode'];
		$model->rememberme = Session::get ( 'rememberme' );
		return 'register';
	}

	/**
	 * @Route ("/register")
	 * @HttpMethod ({"POST"})
	 *
	 * Handle the confirmation request
	 * @param array $params
	 * @throws AppException
	 */
	public function executePost(array $params, ViewModel $model) {
		$authSession = $this->getAuthSession ( $params );
		$userService = UserService::instance ();
		$authService = AuthenticationService::instance ();
		$authSession = $this->getAuthSession ( $params );
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : '';
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : '';
		$rememberme = (isset ( $params ['rememberme'] ) && ! empty ( $params ['rememberme'] )) ? true : false;
		
		try {
			AuthenticationService::instance ()->validateUsername ( $username );
			AuthenticationService::instance ()->validateEmail ( $email );
			if (! empty ( $country )) {
				$countryArr = Country::getCountryByCode ( $country );
				if (empty ( $countryArr )) {
					throw new AppException ( 'Invalid country' );
				}
				$country = $countryArr ['alpha-2'];
			}
			$user = array ();
			$user ['username'] = $username;
			$user ['email'] = $email;
			$user ['userStatus'] = 'Active';
			$user ['country'] = $country;
			$user ['userId'] = $userService->addUser ( $user );
			$userService->addUserAuthProfile ( array (
				'userId' => $user ['userId'],
				'authProvider' => $authSession ['authProvider'],
				'authId' => $authSession ['authId'],
				'authCode' => $authSession ['authCode'],
				'authDetail' => $authSession ['authDetail'] 
			) );
			$authService->handleAuthCredentials ( $authSession );
		} catch ( AppException $e ) {
			$model->title = 'Error';
			$model->username = $username;
			$model->email = $email;
			$model->authProvider = $authSession ['authProvider'];
			$model->code = $authSession ['authCode'];
			$model->error = $e;
			return 'register';
		}
	}

}