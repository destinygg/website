<?php
namespace Destiny\Action;

use Destiny\Service\Leagueapi;
use Destiny\Mimetype;
use Destiny\Utils\Http;

class Summoners {

	public function execute(array $params) {
		$cache = null;
		$response = Leagueapi::getInstance ()->getSummoners ( array ('checkIfModified' => true), $cache);
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $cache->getLastModified() ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode($response) );
	}

}