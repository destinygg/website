<?php
namespace Destiny\Action\League;

use Destiny\Session;
use Destiny\Application;
use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Service\Fantasy\TeamService;
use Destiny\AppException;
use Destiny\ViewModel;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Invites {

	/**
	 * @Route ("/league/invites")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		if (empty ( $teamId )) {
			throw new AppException ( 'Requires a team' );
		}
		$teamService = TeamService::instance ();
		$challengeService = ChallengeService::instance ();
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$model->title = 'Invites';
		$model->user = Session::getCredentials ()->getData ();
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		$model->team = $teamService->getTeamByUserId ( Session::get ( 'userId' ) );
		$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		$model->challengers = $challengeService->getTeamChallengers ( $teamId, 10 );
		$model->invites = $challengeService->getInvites ( $teamId, 5 );
		$model->sentInvites = $challengeService->getSentInvites ( $teamId, 5 );
		return 'league/invites';
	}

}