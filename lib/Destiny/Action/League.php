<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;
use Destiny\Config;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Service\Fantasy\LeaderboardService;
use Destiny\Service\Fantasy\GameService;

class League {

	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		$app = Application::instance ();
		
		$model->title = 'Fantasy League';
		
		$cache = $app->getMemoryCache ( 'champions' );
		$model->champions = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'leaguestatus' );
		$model->leagueServers = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'topteamchampionscores' );
		$model->topChampions = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'teamleaderboard' );
		$model->leaderboard = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'recentgameleaderboard' );
		$model->gameLeaders = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'recentgames' );
		$model->games = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'topsummoners' );
		$model->topSummoners = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'subscriberteamleaderboard' );
		$model->topSubscribers = $cache->read ();
		
		if (! empty ( $teamId )) {
			$model->team = TeamService::instance ()->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = TeamService::instance ()->getTeamChamps ( $teamId );
			$model->invites = ChallengeService::instance ()->getInvites ( $teamId, 5 );
			$model->sentInvites = ChallengeService::instance ()->getSentInvites ( $teamId, 5 );
			$model->userChampScores = LeaderboardService::instance ()->getTeamChampionScores ( $teamId, 5 );
			$model->challengers = ChallengeService::instance ()->getTeamChallengers ( $teamId, 10 );
			if (! empty ( $model->games )) {
				$model->userScores = GameService::instance ()->getTeamGameChampionsScores ( $model->games, Session::get ( 'teamId' ) );
			}
		}
		
		return 'league';
	}

}
