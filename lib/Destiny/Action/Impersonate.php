<?php
namespace Destiny\Action;

use Destiny\Service\AuthenticationService;
use Destiny\Service\UserService;
use Destiny\AppException;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Config;
use Destiny\AppEvent;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Impersonate {

	/**
	 * @Route ("/impersonate")
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function executeGet(array $params, ViewModel $model) {
		$app = Application::instance ();
		if (! Config::$a ['allowImpersonation']) {
			throw new AppException ( 'Impersonating is not allowed' );
		}
		$userId = (isset ( $params ['userId'] ) && ! empty ( $params ['userId'] )) ? $params ['userId'] : '';
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
		if (empty ( $userId ) && empty ( $username )) {
			throw new AppException ( '[username] or [userId] required' );
		}
		$authService = AuthenticationService::instance ();
		$userService = UserService::instance ();
		if (! empty ( $userId )) {
			$user = $userService->getUserById ( $userId );
		} else if (! empty ( $username )) {
			$user = $userService->getUserByUsername ( $username );
		}
		
		if (empty ( $user )) {
			throw new AppException ( 'User not found. Try a different userId or username' );
		}
		
		$authService->login ( $user, 'impersonating' );
		$app->addEvent ( new AppEvent ( array (
			'type' => AppEvent::EVENT_DANGER,
			'label' => sprintf ( 'You are now impersonating [%s]', Session::getCredentials ()->getUsername () ),
			'message' => 'Please be careful' 
		) ) );
		$home = new \Destiny\Action\Home ();
		return $home->execute ( $params, $model );
	}

}
