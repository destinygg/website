<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\TwitchApiService;
use Destiny\Application;

class BroadcastsFeed {

	public function execute(LoggerInterface $log) {
		$response = TwitchApiService::getInstance ()->getPastBroadcasts ()->getResponse ();
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'pastbroadcasts' );
		$cache->write ( $response );
	}

}