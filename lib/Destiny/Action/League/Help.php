<?php
namespace Destiny\Action\League;

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
class Help {

	/**
	 * @Route ("/league/help")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$teamId = Session::get ( 'teamId' );
		$teamService = TeamService::instance ();
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$model->title = 'Help';
		$model->user = Session::getCredentials ()->getData ();
		$model->leagueServers = $cacheDriver->fetch ( 'leaguestatus' );
		if (! empty ( $teamId )) {
			$model->team = $teamService->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = $teamService->getTeamChamps ( $teamId );
		}
		return 'league/help';
	}

}