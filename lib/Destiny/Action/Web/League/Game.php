<?php
namespace Destiny\Action\Web\League;

use Destiny\Common\Service\Fantasy\ChampionService;
use Destiny\Common\Service\Fantasy\LeaderboardService;
use Destiny\Common\AppException;
use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\String;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Game {

	/**
	 * @Route ("/league/game/{gameId}")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamService = TeamService::instance ();
		$challengeService = ChallengeService::instance ();
		$champService = ChampionService::instance ();
		$gameService = GameService::instance ();
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$userId = Session::getCredentials ()->getUserId ();
		$teamId = Session::get ( 'teamId' );
		$gameId = (isset ( $params ['gameId'] ) && ! empty ( $params ['gameId'] )) ? $params ['gameId'] : '';
		if (empty ( $gameId )) {
			throw new AppException ( 'Game not found' );
		}
		$game = $gameService->getGameById ( $gameId );
		if (empty ( $game )) {
			throw new AppException ( 'Game not found' );
		}
		if (empty ( $teamId )) {
			throw new AppException ( 'A team is required' );
		}
		
		$model->title = 'Game';
		$model->user = Session::getCredentials ()->getData ();
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		
		$model->team = $teamService->getTeamByUserId ( $userId );
		$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		
		$game ['champions'] = $gameService->getGameChampions ( $gameId );
		for($x = 0; $x < count ( $game ['champions'] ); $x ++) {
			$game ['champions'] [$x] ['summonerName'] = String::strictUTF8 ( $game ['champions'] [$x] ['summonerName'] );
		}
		$model->game = $game;
		$model->teamChampionScores = $gameService->getTeamGameChampionsScores ( $teamId, $gameId );
		$model->teamGameScores = $gameService->getTeamGameScores ( $teamId, $gameId, 'GAME' );
		
		$gameLeaderboard = LeaderboardService::instance ()->getTeamLeaderboardByGame ( $gameId, 10 );
		foreach ( $gameLeaderboard as $i => $team ) {
			$gameLeaderboard [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $team ['champions'] ) );
		}
		$model->gameLeaderboard = $gameLeaderboard;
		return 'league/game';
	}

}