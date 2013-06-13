<?php

namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Settings;
use Destiny\Service\Fantasy\TeamService;

class Home {

	public function execute(array $params, ViewModel $model) {
		$app = Application::getInstance ();
		if (Session::authorized () && Settings::get ( 'teambar_homepage' )) {
			$model->team = TeamService::getInstance ()->getTeamByUserId ( Session::get ( 'userId' ) );
			$model->teamChamps = TeamService::getInstance ()->getTeamChamps ( $model->team ['teamId'] );
			
			$cache = $app->getMemoryCache ( 'champions' );
			$model->champions = $cache->read ();
		}
		$cache = $app->getMemoryCache ( 'calendarevents' );
		$model->events = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'recentblog' );
		$model->articles = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'summoners' );
		$model->summoners = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'twitter' );
		$model->tweets = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'recenttracks' );
		$model->music = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'youtubeplaylist' );
		$model->playlist = $cache->read ();
		
		$cache = $app->getMemoryCache ( 'pastbroadcasts' );
		$model->broadcasts = $cache->read ();
		
		return 'home';
	}

}
