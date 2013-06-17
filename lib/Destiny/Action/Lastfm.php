<?php

namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\MimeType;
use Destiny\Config;

class Lastfm {

	public function execute(array $params) {
		$app = Application::instance ();
		$tracks = $app->getCacheDriver ()->fetch ( 'recenttracks' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $tracks ) );
	}

}
