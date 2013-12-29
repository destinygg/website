<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;

/**
 * @Controller
 */
class BannedController {

	/**
	 * @Route ("/banned")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function banned(array $params, ViewModel $model) {
		if (Session::hasRole ( UserRole::USER )) {
			$creds = Session::getCredentials ();
			$model->ban = UserService::getUserActiveBan( $creds->getUserId() );
			$model->user = $creds->getData();
		}
		return 'banned';
	}

}
