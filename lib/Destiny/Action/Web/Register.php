<?php
namespace Destiny\Action\Web;

use Destiny\Common\Utils\Country;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Authentication\AuthenticationCredentials;
use Destiny\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Transactional;
use Destiny\Authentication\Service\AuthenticationService;
use Destiny\User\Service\UserService;

/**
 * @Action
 */
class Register {

	/**
	 * Make sure we have a valid session
	 *
	 * @param array $params
	 * @throws Exception
	 * @return AuthenticationCredentials
	 */
	private function getSessionAuthenticationCredentials(array $params) {
		if (! isset ( $params ['code'] ) || empty ( $params ['code'] )) {
			throw new Exception ( 'Invalid code' );
		}
		$authSession = Session::get ( 'authSession' );
		if ($authSession instanceof AuthenticationCredentials) {
			if (empty ( $authSession ) || ($authSession->getAuthCode () != $params ['code'])) {
				throw new Exception ( 'Invalid authentication code' );
			}
			if (! $authSession->isValid ()) {
				throw new Exception ( 'Invalid authentication information' );
			}
		} else {
			throw new Exception ( 'Invalid authentication session' );
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
	 * @throws Exception
	 */
	public function executeGet(array $params, ViewModel $model) {
		$authCreds = $this->getSessionAuthenticationCredentials ( $params );
		$email = $authCreds->getEmail ();
		$username = $authCreds->getUsername ();
		if (! empty ( $username ) && empty ( $email )) {
			$email = $username . '@destiny.gg';
		}
		$model->title = 'New user';
		$model->username = $username;
		$model->email = $email;
		$model->follow = (isset($params['follow'])) ? $params['follow']:'';
		$model->authProvider = $authCreds->getAuthProvider ();
		$model->code = $authCreds->getAuthCode ();
		$model->rememberme = Session::get ( 'rememberme' );
		return 'register';
	}

	/**
	 * @Route ("/register")
	 * @HttpMethod ({"POST"})
	 * @Transactional
	 *
	 * Handle the confirmation request
	 * @param array $params
	 * @throws Exception
	 */
	public function executePost(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$authService = AuthenticationService::instance ();
		$authCreds = $this->getSessionAuthenticationCredentials ( $params );
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : '';
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : '';
		$rememberme = (isset ( $params ['rememberme'] ) && ! empty ( $params ['rememberme'] )) ? true : false;
		
		$authCreds->setUsername ( $username );
		$authCreds->setEmail ( $email );
		
		try {
			AuthenticationService::instance ()->validateUsername ( $username );
			AuthenticationService::instance ()->validateEmail ( $email );
			if (! empty ( $country )) {
				$countryArr = Country::getCountryByCode ( $country );
				if (empty ( $countryArr )) {
					throw new Exception ( 'Invalid country' );
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
				'authProvider' => $authCreds->getAuthProvider (),
				'authId' => $authCreds->getAuthId (),
				'authCode' => $authCreds->getAuthCode (),
				'authDetail' => $authCreds->getAuthDetail () 
			) );
			Session::set ( 'authSession' );
			$authCredHandler = new AuthenticationRedirectionFilter ();
			return $authCredHandler->execute ( $authCreds );
		} catch ( Exception $e ) {
			$model->title = 'Error';
			$model->username = $username;
			$model->email = $email;
			$model->follow = (isset($params['follow'])) ? $params['follow']:'';
			$model->authProvider = $authCreds->getAuthProvider ();
			$model->code = $authCreds->getAuthCode ();
			$model->error = $e;
			return 'register';
		}
	}

}