<?php

namespace Destiny\Action;

use Destiny\Utils\Country;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Session;
use Destiny\Application;
use Destiny\AppException;
use Destiny\Config;
use Destiny\OAuthClient;
use Destiny\Service\AuthenticationService;
use Destiny\Service\UserService;

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
			throw new AppException ( 'Invalid authentication session' );
		}
		if (empty ( $authSession ['authProvider'] ) || empty ( $authSession ['authCode'] ) || empty ( $authSession ['authId'] )) {
			throw new AppException ( 'Invalid authentication session' );
		}
		return $authSession;
	}

	/**
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
	 * Handle the confirmation request
	 *
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
					'authToken' => $authSession ['authCode'] 
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