<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\String;
use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\LeaderboardService;
use Destiny\Service\Fantasy\ChampionService;
use Destiny\Service\Fantasy\GameService;

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
		$champService = ChampionService::instance ();
		$leaders = LeaderboardService::instance ()->getRecentGameLeaderboard ( 10 );
		foreach ( $leaders as $i => $leader ) {
			$leaders [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $leader ['champions'] ) );
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