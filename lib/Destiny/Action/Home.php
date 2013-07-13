<?php
namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Home {

	/**
	 * @Route ("/")
	 * @Route ("/home")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$cacheDriver = $app->getCacheDriver ();
		$model->events = $cacheDriver->fetch ( 'calendarevents' );
		$model->articles = $cacheDriver->fetch ( 'recentblog' );
		$model->summoners = $cacheDriver->fetch ( 'summoners' );
		$model->tweets = $cacheDriver->fetch ( 'twitter' );
		$model->music = $cacheDriver->fetch ( 'recenttracks' );
		$model->playlist = $cacheDriver->fetch ( 'youtubeplaylist' );
		$model->broadcasts = $cacheDriver->fetch ( 'pastbroadcasts' );
		$model->streamInfo = $cacheDriver->fetch ( 'streaminfo' );
		return 'home';
	}

}
