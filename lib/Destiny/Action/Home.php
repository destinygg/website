<?php

namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;

class Home {

	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$cacheDriver = $app->getCacheDriver ();
		if (Session::hasRole ( \Destiny\UserRole::USER ) && Session::hasFeature(\Destiny\UserFeature::STICKY_TEAMBAR)) {
			$model->user = Session::getCredentials ()->getData ();
			$model->team = TeamService::instance ()->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = TeamService::instance ()->getTeamChamps ( $model->team ['teamId'] );
			$model->champions = $cacheDriver->fetch ( 'champions' );
		}
		$model->events = $cacheDriver->fetch ( 'calendarevents' );
		$model->articles = $cacheDriver->fetch ( 'recentblog' );
		$model->summoners = $cacheDriver->fetch ( 'summoners' );
		$model->tweets = $cacheDriver->fetch ( 'twitter' );
		$model->music = $cacheDriver->fetch ( 'recenttracks' );
		$model->playlist = $cacheDriver->fetch ( 'youtubeplaylist' );
		$model->broadcasts = $cacheDriver->fetch ( 'pastbroadcasts' );
		return 'home';
	}

}
