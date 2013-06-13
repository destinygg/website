<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\LeagueApiService;
use Destiny\Application;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'summoners' );
		$response = LeagueApiService::getInstance ()->getSummoners ();
		$cache->write ( $response );
	}

}
