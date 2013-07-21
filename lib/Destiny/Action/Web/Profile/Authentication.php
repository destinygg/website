<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\Service\ApiAuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Authentication {

	/**
	 * @Route ("/profile/authentication")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$userId = Session::get ( 'userId' );
		$model->title = 'Authentication';
		$model->user = $userService->getUserById ( $userId );
		
		// Build a list of profile types for UI purposes
		$authProfiles = $userService->getAuthProfilesByUserId ( $userId );
		$authProfileTypes = array ();
		if (! empty ( $authProfiles )) {
			foreach ( $authProfiles as $profile ) {
				$authProfileTypes [] = $profile ['authProvider'];
			}
			$model->authProfiles = $authProfiles;
			$model->authProfileTypes = $authProfileTypes;
		}
		
		$model->authTokens = ApiAuthenticationService::instance ()->getAuthTokensByUserId ( $userId );
		return 'profile/authentication';
	}

}