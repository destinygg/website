<?php

namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\Cache\Apc;
use Destiny\Mimetype;
use Destiny\Config;

class Lastfm {

	public function execute(array $params) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'recenttracks' );
		Http::checkIfModifiedSince ( $cache->getLastModified (), true );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $cache->getLastModified () ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $cache->read () ) );
	}

}
