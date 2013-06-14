<?php

namespace Destiny\Action;

use Destiny\Mimetype;
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\Config;

class Youtube {

	public function execute(array $params) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'youtubeplaylist' );
		$response = Youtube::instance ()->getYoutubePlaylist ()->getResponse ();
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $cache->getLastModified () ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( $response );
	}

}