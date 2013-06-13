<?php

namespace Destiny\Action;

use Destiny\Application;
use Destiny\ViewModel;
use Destiny\Config;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Service\Fantasy\LeaderboardService;
use Destiny\Service\Leagueapi;

class League {

	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		$app = Application::getInstance ();
		
		$model->title = 'Fantasy League';
		if (! empty ( $teamId )) {
			$model->team = TeamService::getInstance ()->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = TeamService::getInstance ()->getTeamChamps ( $teamId );
			$model->invites = ChallengeService::getInstance ()->getInvites ( $teamId, 5 );
			$model->sentInvites = ChallengeService::getInstance ()->getSentInvites ( $teamId, 5 );
			$model->userChampScores = LeaderboardService::getInstance ()->getTeamChampionScores ( $teamId, 5 );
			$model->challengers = ChallengeService::getInstance ()->getTeamChallengers ( $teamId, 10 );
		}
		
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
		return 'league';
	}

}
