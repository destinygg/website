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
		$cacheDriver = $app->getCacheDriver ();
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == false) {
				continue;
			}
			$log->debug ( $summoner ['name'] . ' checking ingame' );
			$ingameData = $leagueApiService->getInGameProgress ( $summoner );
			if (! empty ( $ingameData ) && isset ( $ingameData ['success'] ) && $ingameData ['success'] == true && $ingameData ['data'] != null) {
				$log->debug ( '' . $summoner ['name'] . ' game found ' . $ingameData ['data'] ['gameId'] );
				$ftrackService->trackIngameProgress ( $summoner, $ingameData ['data'] );
			}
			$track = $ftrackService->getTrackedProgressById ( $ingameData ['data'] ['gameId'] );
			$cacheDriver->save ( 'ingame.' . $summoner ['id'], $track );
		}
		$log->debug ( 'Ended ingame progress tracking' );
	}

}