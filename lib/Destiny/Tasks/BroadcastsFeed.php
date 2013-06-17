<?php

namespace Destiny\Tasks;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\TwitchApiService;
use Destiny\Application;

class BroadcastsFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
		$app->getCacheDriver ()->save ( 'pastbroadcasts', $response );
	}

}