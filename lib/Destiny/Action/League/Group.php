<?php
namespace Destiny\Action\League;

use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Application;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Group {

	/**
	 * @Route ("/league/group")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		$teamService = TeamService::instance ();
		$challengeService = ChallengeService::instance ();
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$model->title = 'Group';
		$model->user = Session::getCredentials ()->getData ();
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		$model->challengers = $challengeService->getTeamChallengers ( $teamId, 10 );
		if (! empty ( $teamId )) {
			$model->team = $teamService->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		}
		return 'league/group';
	}

}