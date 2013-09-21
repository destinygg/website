<?php
namespace Destiny\Action\Web;

use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Authentication\Service\AuthenticationService;

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