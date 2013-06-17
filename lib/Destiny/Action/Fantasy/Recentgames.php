<?php

namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\GameService;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Config;

class Recentgames {

	public function execute(array $params) {
		$response = null;
		$game = GameService::instance ()->getRecentGameData ();
		$aggregateDate = Date::getDateTime ( $game ['aggregatedDate'] );
		Http::checkIfModifiedSince ( $aggregateDate->getTimestamp (), true );
		Http::header ( Http::HEADER_LAST_MODIFIED, $aggregateDate->format ( 'r' ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( array (
				'date' => $aggregateDate->format ( Date::FORMAT ) 
		) ) );
	}

}