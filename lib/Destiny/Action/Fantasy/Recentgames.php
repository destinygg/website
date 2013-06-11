<?php

namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\Db\Game;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Recentgames {

	public function execute(array $params) {
		$response = null;
		$game = Game::getInstance ()->getRecentGameData ();
		$aggregateDate = new \DateTime ( $game ['aggregatedDate'] );
		Http::checkIfModifiedSince ( $aggregateDate->getTimestamp (), true );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $aggregateDate->getTimestamp () ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( array (
				'date' => $aggregateDate->format ( Date::FORMAT ) 
		) ) );
	}

}