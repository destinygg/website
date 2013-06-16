<?php

namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Application;
use Destiny\Config;

class Champions {

	public function execute(array $params) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'champions' );
		$champions = $cache->read ();
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $champions ) );
	}

}