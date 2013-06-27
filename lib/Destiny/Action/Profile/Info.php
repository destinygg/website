<?php
namespace Destiny\Action\Profile;

use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\MimeType;

class Info {

	public function execute(array $params) {
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( Session::getCredentials ()->getData () ) );
	}

}