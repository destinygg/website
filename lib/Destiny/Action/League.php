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
		$cacheDriver = $app->getCacheDriver ();
		
		$model->title = 'Fantasy League';
		$model->user = Session::getAuthCreds ()->getCredentials ();
		$model->champions = $cacheDriver->fetch ( 'champions' );
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		$model->topChampions = $cacheDriver->fetch ( 'topteamchampionscores' );
		$model->leaderboard = $cacheDriver->fetch ( 'teamleaderboard' );
		$model->gameLeaders = $cacheDriver->fetch ( 'recentgameleaderboard' );
		$model->games = $cacheDriver->fetch ( 'recentgames' );
		$model->topSummoners = $cacheDriver->fetch ( 'topsummoners' );
		$model->topSubscribers = $cacheDriver->fetch ( 'subscriberteamleaderboard' );
		
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
