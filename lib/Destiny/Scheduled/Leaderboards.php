<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\String;
use Psr\Log\LoggerInterface;
use Destiny\Service\Leagueapi;
use Destiny\Service\Fantasy\Db\Tracking;
use Destiny\Service\Fantasy\Db\Leaderboard;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Service\Fantasy\Db\Game;

class Leaderboards {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$champService = Champion::getInstance ();
		$leadersService = Leaderboard::getInstance ();
		
		// Subs leaderboard
		$teams = $leadersService->getSubscriberTeamLeaderboard ( 10 );
		foreach ( $teams as $i => $team ) {
			$teams [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $teams [$i] ['champions'] ) );
		}
		$cache = $app->getMemoryCache ( 'subscriberteamleaderboard' );
		$cache->write ( $teams );
		
		// Top summoners
		$summoners = $leadersService->getTopSummoners ( 10 );
		foreach ( $summoners as $i => $summoner ) {
			$summoners [$i] ['summonerName'] = String::strictUTF8 ( $summoners [$i] ['summonerName'] );
			$summoners [$i] ['mostPlayedChampion'] = $champService->getChampionById ( $summoners [$i] ['mostPlayedChampion'] );
		}
		$cache = $app->getMemoryCache ( 'topsummoners' );
		$cache->write ( $summoners );
		
		// Recent games
		$gameService = Game::getInstance ();
		$games = $gameService->getRecentGames ( 3 );
		foreach ( $games as $i => $game ) {
			$games [$i] ['champions'] = $gameService->getGameChampions ( $game ['gameId'] );
			for($x = 0; $x < count ( $games [$i] ['champions'] ); $x ++) {
				$games [$i] ['champions'] [$x] ['summonerName'] = String::strictUTF8 ( $games [$i] ['champions'] [$x] ['summonerName'] );
			}
		}
		$cache = $app->getMemoryCache ( 'recentgames' );
		$cache->write ( $games );
		
		// Recent game leaderboard
		$champService = Champion::getInstance ();
		$leaders = Leaderboard::getInstance ()->getRecentGameLeaderboard ( 10 );
		foreach ( $leaders as $i => $leader ) {
			$leaders [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $leader ['champions'] ) );
		}
		$cache = $app->getMemoryCache ( 'recentgameleaderboard' );
		$cache->write ( $leaders );
		
		// Team Leaderboard
		$champService = Champion::getInstance ();
		$teams = Leaderboard::getInstance ()->getTeamLeaderboard ( 10 );
		foreach ( $teams as $i => $team ) {
			$teams [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $team ['champions'] ) );
		}
		$cache = $app->getMemoryCache ( 'teamleaderboard' );
		$cache->write ( $teams );
		
		// Top team champion scores
		$topScorers = Leaderboard::getInstance ()->getTopTeamChampionScores ( 5 );
		$cache = $app->getMemoryCache ( 'topteamchampionscores' );
		$cache->write ( $topScorers );
	}

}