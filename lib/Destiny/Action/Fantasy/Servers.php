<?php

namespace Destiny\Action\Fantasy;

use Destiny\Application;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Config;

class Servers {

	public function execute(array $params) {
		$app = Application::instance ();
		$stats = $app->getCacheDriver ()->fetch ( 'leaguestatus' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $stats ) );
	}

}