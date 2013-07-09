<?php
namespace Destiny\Action\Admin\User;

use Destiny\Service\UserService;
use Destiny\AppException;
use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\MimeType;

class Find {

	public function execute(array $params) {
		$users = UserService::instance ()->findUserByUsername ( $params ['username'] );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $users ) );
		exit ();
	}

}
	