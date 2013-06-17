<?php

namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Application;
use Destiny\Config;

class Champions {

	public function execute(array $params) {
		$champions = Application::instance ()->getCacheDriver ()->fetch ( 'champions' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $champions ) );
	}

}