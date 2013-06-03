<?php
namespace Destiny\Cron;

use Destiny\Config;
use Destiny\Logger;
use Destiny\Service\Leagueapi;
use Destiny\Service\Fantasy\Db\Tracking;

class Recentgames {

	public function execute(Logger $log) {
		$log->log ( 'Tracking recent games' );
		$ftrackService = Tracking::getInstance ();
		$leagueApiService = Leagueapi::getInstance ();
		foreach ( Config::$a ['lol'] ['summoners'] as $rSummoner ) {
			if ($rSummoner ['track'] == false) {
				continue;
			}
			$log->log ( 'Summoner: ' . $rSummoner ['name'] );
			$recentGames = $leagueApiService->getRecentGames ( $rSummoner, 5 );
			if ($recentGames != null && $recentGames->success == true) {
				foreach ( $recentGames->data as $i => $recentGame ) {
					if (false == $ftrackService->isGameRecorded ( $recentGame->gameId )) {
						$log->log ( 'Summoner: ' . $rSummoner ['name'] . ' Saving Game: ' . $recentGame->gameId );
						$ftrackService->persistGame ( $recentGame, $rSummoner );
					}
				}
			}
		}
		$log->log ( 'Tracking complete' );
	}

}