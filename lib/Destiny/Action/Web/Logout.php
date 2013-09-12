<?php
namespace Destiny\Action\Web;

use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

/**
 * @Action
 */
class Logout {

	/**
	 * @Route ("/logout")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		AuthenticationService::instance ()->logout ();
		return 'redirect: /';
	}

}