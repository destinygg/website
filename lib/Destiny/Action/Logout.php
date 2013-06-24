<?php

namespace Destiny\Action;

use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\Session;
use Destiny\AppException;
use Destiny\Utils\Http;

class Logout {

	public function execute(array $params) {
		AuthenticationService::instance ()->logout ();
		Http::header ( Http::HEADER_LOCATION, '/' );
		exit ();
	}

}