<?php

namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Application;
use Destiny\Config;

class Champions {

	public function execute(array $params) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'champions' );
		$champions = $cache->read ();
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $champions ) );
	}

}