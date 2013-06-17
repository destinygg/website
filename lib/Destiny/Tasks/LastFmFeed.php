<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class LastFmFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = CommonApiService::instance ()->getLastFMTracks ()->getResponse ();
		$app->getCacheDriver ()->save ( 'recenttracks', $response );
	}

}