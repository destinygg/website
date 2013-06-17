<?php

namespace Destiny\Action;

use Destiny\MimeType;
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\Config;

class Youtube {

	public function execute(array $params) {
		$app = Application::instance ();
		$playlist = $app->getCacheDriver ()->fetch ( 'youtubeplaylist' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $playlist ) );
	}

}