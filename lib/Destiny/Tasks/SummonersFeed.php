<?php

namespace Destiny\Tasks;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\LeagueApiService;
use Destiny\Application;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'summoners' );
		$response = LeagueApiService::instance ()->getSummoners ();
		$cache->write ( $response );
	}

}
