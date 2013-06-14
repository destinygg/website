<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\TwitchApiService;
use Destiny\Application;

class BroadcastsFeed {

	public function execute(LoggerInterface $log) {
		$response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'pastbroadcasts' );
		$cache->write ( $response );
	}

}