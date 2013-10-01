<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;

/**
 * @Controller
 */
class HomeController {

	/**
	 * @Route ("/")
	 * @Route ("/home")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function home(array $params, ViewModel $model) {
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

	/**
	 * @Route ("/help/agreement")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function helpAgreement(array $params, ViewModel $model) {
		$model->title = 'User agreement';
		return 'help/agreement';
	}

	/**
	 * @Route ("/schedule")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function schedule(array $params, ViewModel $model) {
		$model->title = 'Schedule';
		return 'schedule';
	}

	/**
	 * @Route ("/ping")
	 *
	 * @param array $params
	 */
	public function ping(array $params) {
		$response = new HttpEntity ( Http::STATUS_OK );
		$response->addHeader ( 'X-Pong', 'Destiny' );
		return $response;
	}

	/**
	 * @Route ("/bigscreen")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function bigscreen(array $params, ViewModel $model) {
		$model->streamInfo = Application::instance ()->getCacheDriver ()->fetch ( 'streaminfo' );
		return 'bigscreen';
	}

}
