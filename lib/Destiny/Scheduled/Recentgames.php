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
		foreach ( Config::$a ['lol'] ['summoners'] as $rSummoner ) {
			if ($rSummoner ['track'] == false) {
				continue;
			}
			$log->info ( 'Summoner: ' . $rSummoner ['name'] );
			$recentGames = $leagueApiService->getRecentGames ( $rSummoner, 5 );
			if ($recentGames != null && $recentGames ['success'] == true) {
				foreach ( $recentGames ['data'] as $i => $recentGame ) {
					if (false == $ftrackService->isGameRecorded ( $recentGame ['gameId'] )) {
						$log->info ( 'Summoner: ' . $rSummoner ['name'] . ' Saving Game: ' . $recentGame ['gameId'] );
						$ftrackService->persistGame ( $recentGame, $rSummoner );
					}
				}
			}
		}
		$log->info ( 'Tracking complete' );
	}

}