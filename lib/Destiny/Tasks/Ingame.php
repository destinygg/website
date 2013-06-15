<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Destiny\Service\LeagueApiService;
use Destiny\Service\Fantasy\GameTrackingService;
use Psr\Log\LoggerInterface;

class Ingame {

	public function execute(LoggerInterface $log) {
		$log->debug ( 'Tracking ingame progress' );
		$ftrackService = GameTrackingService::instance ();
		$leagueApiService = LeagueApiService::instance ();
		$app = Application::instance ();
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == false) {
				continue;
			}
			$log->debug ( $summoner ['name'] . ' checking ingame' );
			$ingame = $leagueApiService->getInGameProgress ( $summoner );
			
			$cache = $app->getMemoryCache ( 'ingame.' . $summoner ['id'] );
			$cache->write ( $ingame );
			
			if ($ingame != null && $ingame ['success'] == true && $ingame ['data'] != null) {
				$log->debug ( '' . $summoner ['name'] . ' game found ' . $ingame ['data'] ['gameId'] );
				$ftrackService->trackIngameProgress ( $summoner, $ingame ['data'] );
			}
		}
		$log->debug ( 'Ended ingame progress tracking' );
	}

}