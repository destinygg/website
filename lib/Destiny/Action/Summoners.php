<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\Config;
use Destiny\MimeType;
use Destiny\Utils\Http;

class Summoners {

	public function execute(array $params) {
		$app = Application::instance ();
		$summoners = $app->getCacheDriver ()->fetch ( 'summoners' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $summoners ) );
	}

}