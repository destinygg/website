<?php
namespace Destiny\Action\Web\League;

use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\AppException;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		$model->team = $teamService->getTeamByUserId ( Session::getCredentials ()->getUserId () );
		$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		$model->challengers = $challengeService->getTeamChallengers ( $teamId, 10 );
		$model->invites = $challengeService->getInvites ( $teamId, 5 );
		$model->sentInvites = $challengeService->getSentInvites ( $teamId, 5 );
		return 'league/invites';
	}

}