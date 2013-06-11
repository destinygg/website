<?php

namespace Destiny\Action\Fantasy;

use Destiny\Application;
use Destiny\Service\Leagueapi;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Servers {

	public function execute(array $params) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'leaguestatus' );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $cache->getLastModified () ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $cache->read () ) );
	}

}