<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Session;
use Destiny\Common\User\UserService;

/**
 * @Controller
 */
class BannedController {
	
	/**
	 * @Route ("/banned")
	 * @Secure ({"USER"})
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @return string
	 */
	public function banned(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$creds = Session::getCredentials ();
		$model->ban = $userService->getUserActiveBan ( $creds->getUserId (), $_SERVER['REMOTE_ADDR'] );
		$model->banType = 'none';
		if (! empty ( $model->ban )) {
			if (! $model->ban ['endtimestamp']) {
				$model->banType = 'permanent';
			} else {
				$model->banType = 'temporary';
			}
		}
		$model->user = $creds->getData ();
		return 'banned';
	}
}
