<?php

namespace Destiny\Tasks;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\LeagueApiService;
use Destiny\Application;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = LeagueApiService::instance ()->getSummoners ();
		$app->getCacheDriver ()->save ( 'summoners', $response );
	}

}
