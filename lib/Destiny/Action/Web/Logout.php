<?php
namespace Destiny\Action\Web;

use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Application;
use Destiny\Common\Session;
use Destiny\Common\AppException;
use Destiny\Common\Utils\Http;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		Http::header ( Http::HEADER_LOCATION, '/' );
		exit ();
	}

}