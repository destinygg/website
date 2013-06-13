<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Leagueapi;
use Destiny\Service\Fantasy\Db\Tracking;

class Recentgames {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Tracking recent games' );
		$ftrackService = Tracking::getInstance ();
		$leagueApiService = Leagueapi::getInstance ();
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