<?php
namespace Destiny\Cron\Action;

use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Destiny\Common\Service\Fantasy\GameTrackingService;
use Psr\Log\LoggerInterface;

class Recentgames {

	public function execute(LoggerInterface $log) {
		$log->debug ( 'Tracking recent games' );
		$ftrackService = GameTrackingService::instance ();
		$leagueApiService = LeagueApiService::instance ();
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == false) {
				continue;
			}
			$log->debug ( 'Summoner: ' . $summoner ['name'] );
			
			$recordedGames = GameService::instance ()->getRecentGames ( 10, 0 );
			$recordedGamesId = array ();
			foreach ( $recordedGames as $game ) {
				$recordedGamesId [] = $game ['gameId'];
			}
			
			$recentGames = $leagueApiService->getRecentGames ( $summoner, 5, $recordedGamesId );
			if ($recentGames != null && $recentGames ['success'] == true) {
				foreach ( $recentGames ['data'] as $i => $recentGame ) {
					if (false == $ftrackService->isGameRecorded ( $recentGame ['gameId'] )) {
						$log->debug ( 'Summoner: ' . $summoner ['name'] . ' Saving Game: ' . $recentGame ['gameId'] );
						$ftrackService->persistGame ( $recentGame, $summoner );
					}
				}
			}
		}
		$log->debug ( 'Tracking complete' );
	}

}