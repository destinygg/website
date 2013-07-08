<?php
namespace Destiny\Action\Admin;

use Destiny\Service\UserFeaturesService;
use Destiny\AppException;
use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\Fantasy\GameService;

class User {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		$user ['roles'] = UserService::instance ()->getUserRoles ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getFeatures ();
		return 'admin/user';
	}

}
