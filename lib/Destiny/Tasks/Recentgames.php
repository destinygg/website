<?php

namespace Destiny\Tasks;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\LeagueApiService;
use Destiny\Service\Fantasy\GameTrackingService;

class Recentgames {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Tracking recent games' );
		$ftrackService = GameTrackingService::instance ();
		$leagueApiService = LeagueApiService::instance ();
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == false) {
				continue;
			}
			$log->info ( 'Summoner: ' . $summoner ['name'] );
			$recentGames = $leagueApiService->getRecentGames ( $summoner, 5 );
			if ($recentGames != null && $recentGames ['success'] == true) {
				foreach ( $recentGames ['data'] as $i => $recentGame ) {
					if (false == $ftrackService->isGameRecorded ( $recentGame ['gameId'] )) {
						$log->info ( 'Summoner: ' . $summoner ['name'] . ' Saving Game: ' . $recentGame ['gameId'] );
						$ftrackService->persistGame ( $recentGame, $summoner );
					}
				}
			}
		}
		$log->info ( 'Tracking complete' );
	}

}