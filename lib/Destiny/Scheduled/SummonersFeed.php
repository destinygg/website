<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Leagueapi;
use Destiny\Application;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'summoners' );
		$response = Leagueapi::getInstance ()->getSummoners ();
		$cache->write ( $response );
	}

}
