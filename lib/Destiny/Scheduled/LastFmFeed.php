<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Lastfm;

class LastFmFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$response = Lastfm::getInstance ()->getRecentTracks ()->getResponse ();
		$cache = $app->getMemoryCache ( 'recenttracks' );
		$cache->write ( $response );
	}

}