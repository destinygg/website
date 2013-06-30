<?php
namespace Destiny\Action;

use Destiny\Service\Fantasy\ChampionService;
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
		$userId = Session::get ( 'userId' );
		$app = Application::instance ();
		$cacheDriver = $app->getCacheDriver ();
		$champService = ChampionService::instance ();
		$leaderService = LeaderboardService::instance ();
		$teamService = TeamService::instance ();
		$challengeService = ChallengeService::instance ();
		$gameService = GameService::instance ();
		$model->title = 'Fantasy League';
		$model->user = Session::getCredentials ()->getData ();
		$model->champions = $cacheDriver->fetch ( 'champions' );
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		$model->leaderboard = $cacheDriver->fetch ( 'teamleaderboard' );
		$model->gameLeaders = $cacheDriver->fetch ( 'recentgameleaderboard' );
		$model->topSummoners = $cacheDriver->fetch ( 'topsummoners' );
		$model->topSubscribers = $cacheDriver->fetch ( 'subscriberteamleaderboard' );
		$model->topChampions = $cacheDriver->fetch ( 'topteamchampionscores' );
		$model->ingame = null;
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			if ($summoner ['track'] == true) {
				$ingame = $cacheDriver->fetch ( 'ingame.' . $summoner ['id'] );
				if ($ingame != null && $ingame ['gameData'] != null) {
					$model->ingame = $ingame;
					break;
				}
			}
		}
		if (! empty ( $teamId )) {
			$model->team = $teamService->getTeamByUserId ( $userId );
			$model->teamChamps = $teamService->getTeamChamps ( $teamId );
			$model->topTeamChampions = $leaderService->getTeamTopChampions ( $teamId, 10 );
			$model->teamGameScores = $leaderService->getTeamGameScores ( $teamId, 10 );
			$games = array ();
			foreach ( $model->teamGameScores as $i => $game ) {
				$games [] = $game ['gameId'];
			}
			if (! empty ( $games )) {
				$model->teamGameChampScores = $leaderService->getTeamGameChampScores ( $games );
			}
			$model->invites = $challengeService->getInvites ( $teamId, 5 );
		}
		
		$model->endTime = new \DateTime ( '2013-06-30T23:59:59+06:00' );
		$model->leagueEnded = ($model->endTime < new \DateTime ());
		return 'league';
	}

}
