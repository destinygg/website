<?php
namespace Destiny\Action\Web\League;

use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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