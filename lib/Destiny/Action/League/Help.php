<?php

namespace Destiny\Action\League;

use Destiny\Application;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Session;
use Destiny\ViewModel;

class Help {

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