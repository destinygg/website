<?php
namespace Destiny\Action\Web;

use Destiny\Common\Service\Fantasy\ChampionService;
use Destiny\Common\Application;
use Destiny\Common\ViewModel;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Service\Fantasy\LeaderboardService;
use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class League {

	/**
	 * @Route ("/league")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		$userId = Session::getCredentials ()->getUserId ();
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
		return 'league';
	}

}
