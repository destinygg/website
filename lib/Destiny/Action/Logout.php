<?php
namespace Destiny\Action;

use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\Session;
use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

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