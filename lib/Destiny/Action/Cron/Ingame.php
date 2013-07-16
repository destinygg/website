<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Destiny\Common\Service\Fantasy\GameTrackingService;
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
			
			// This just gets the latest game, which will break if we have multiple chars we track all playing diff games at the same time
			// Since we dont have multiple, this will save bandwidth
			$trackedGameId = 0;
			$lastTrackedGames = $ftrackService->getTrackedGames ( 1 );
			if (! empty ( $lastTrackedGames ) && isset ( $lastTrackedGames [0] )) {
				$trackedGameId = $lastTrackedGames [0] ['gameId'];
			}
			
			// Pass in the last game Id, if the current ingame is the same, the API doesnt send the data back
			$ingameData = $leagueApiService->getInGameProgress ( $summoner, $trackedGameId );
			
			if (! empty ( $ingameData ) && isset ( $ingameData ['success'] ) && $ingameData ['success'] == true) {
				// New game data, if the data is null, it means the game is in progress, but we have already tracked it
				if (isset ( $ingameData ['data'] ) && ! empty ( $ingameData ['data'] )) {
					$log->debug ( '' . $summoner ['name'] . ' game found ' . $ingameData ['data'] ['gameId'] );
					$ftrackService->trackIngameProgress ( $summoner, $ingameData ['data'] );
					$track = $ftrackService->getTrackedProgressById ( $ingameData ['data'] ['gameId'] );
					$cacheDriver->save ( 'ingame.' . $summoner ['id'], $track );
				}
			} else {
				// Not in game, clear the ingame data
				$cacheDriver->save ( 'ingame.' . $summoner ['id'], null );
			}
		}
		$log->debug ( 'Ended ingame progress tracking' );
	}

}