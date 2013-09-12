<?php
namespace Destiny\Action\Web;

use Destiny\Common\Service\ChatIntegrationService;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\Exception;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\AppEvent;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;

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
	 * @throws Exception
	 * @return string
	 */
	public function executeGet(array $params, ViewModel $model) {
		$app = Application::instance ();
		if (! Config::$a ['allowImpersonation']) {
			throw new Exception ( 'Impersonating is not allowed' );
		}
		$userId = (isset ( $params ['userId'] ) && ! empty ( $params ['userId'] )) ? $params ['userId'] : '';
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
		if (empty ( $userId ) && empty ( $username )) {
			throw new Exception ( '[username] or [userId] required' );
		}
		$authService = AuthenticationService::instance ();
		$userService = UserService::instance ();
		if (! empty ( $userId )) {
			$user = $userService->getUserById ( $userId );
		} else if (! empty ( $username )) {
			$user = $userService->getUserByUsername ( $username );
		}
		
		if (empty ( $user )) {
			throw new Exception ( 'User not found. Try a different userId or username' );
		}
		
		$credentials = $authService->getUserCredentials ( $user, 'impersonating' );
		Session::start ( Session::START_NOCOOKIE );
		Session::updateCredentials ( $credentials );
		ChatIntegrationService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
		
		$app->addEvent ( new AppEvent ( array (
			'type' => AppEvent::EVENT_DANGER,
			'label' => sprintf ( 'You are now impersonating [%s]', Session::getCredentials ()->getUsername () ),
			'message' => 'Please be careful' 
		) ) );
		$home = new \Destiny\Action\Web\Home ();
		return $home->execute ( $params, $model );
	}

}
