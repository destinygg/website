<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\String;
use Destiny\Common\Service\Fantasy\LeaderboardService;
use Destiny\Common\Service\Fantasy\ChampionService;
use Destiny\Common\Service\Fantasy\GameService;
use Psr\Log\LoggerInterface;

class Leaderboards {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$champService = ChampionService::instance ();
		$leadersService = LeaderboardService::instance ();
		$cacheDriver = $app->getCacheDriver ();
		
		// Subs leaderboard
		$teams = $leadersService->getSubscriberTeamLeaderboard ( 10 );
		foreach ( $teams as $i => $team ) {
			$teams [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $teams [$i] ['champions'] ) );
		}
		$cacheDriver->save ( 'subscriberteamleaderboard', $teams );
		
		// Top summoners
		$summoners = $leadersService->getTopSummoners ( 10 );
		foreach ( $summoners as $i => $summoner ) {
			$summoners [$i] ['summonerName'] = String::strictUTF8 ( $summoners [$i] ['summonerName'] );
			$summoners [$i] ['mostPlayedChampion'] = $champService->getChampionById ( $summoners [$i] ['mostPlayedChampion'] );
		}
		$cacheDriver->save ( 'topsummoners', $summoners );
		
		// Recent game leaderboard
		$leaders = array ();
		$game = GameService::instance ()->getRecentGameData ();
		if (! empty ( $game )) {
			$leaders = LeaderboardService::instance ()->getGameLeaderboard ( $game ['gameId'], 10 );
			foreach ( $leaders as $i => $leader ) {
				$leaders [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $leader ['champions'] ) );
			}
		}
		$cacheDriver->save ( 'recentgameleaderboard', $leaders );
		
		// Team Leaderboard
		$champService = ChampionService::instance ();
		$teams = LeaderboardService::instance ()->getTeamLeaderboard ( 10 );
		foreach ( $teams as $i => $team ) {
			$teams [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $team ['champions'] ) );
		}
		$cacheDriver->save ( 'teamleaderboard', $teams );
		
		// Top team champion scores
		$topScorers = LeaderboardService::instance ()->getTopTeamChampionScores ( 10 );
		$cacheDriver->save ( 'topteamchampionscores', $topScorers );
		
		$log->debug ( 'Reset leaderboards' );
	}

}