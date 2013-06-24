<?php

namespace Destiny\Action\Profile;

use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;

class Authentication {

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
		return 'profile/authentication';
	}

}