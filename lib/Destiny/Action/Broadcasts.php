<?php

namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Application;
use Destiny\Config;

class Broadcasts {

	public function execute(array $params) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'pastbroadcasts' );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $cache->getLastModified () ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $cache->read () ) );
	}

}