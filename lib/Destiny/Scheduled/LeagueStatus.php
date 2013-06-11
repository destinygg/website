<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Leagueapi;
use Destiny\Service\Fantasy\Db\Tracking;

class LeagueStatus {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Updated lol status' );
		$response = Leagueapi::getInstance ()->getStatus ()->getResponse ();
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'leaguestatus' );
		$cache->write ( $response );
	}

}