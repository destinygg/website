<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\LeagueApiService;

class LeagueStatus {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Updated lol status' );
		$response = LeagueApiService::instance ()->getStatus ()->getResponse ();
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'leaguestatus' );
		$cache->write ( $response );
	}

}