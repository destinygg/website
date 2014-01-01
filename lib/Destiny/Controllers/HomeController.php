<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;

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

	/**
	 * @Route ("/emotes")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function emoticons(array $params, ViewModel $model) {
		$model->emoticons = Config::$a['chat'] ['customemotes'];
		return 'chat/emoticons';
	}

	/**
	 * @Route ("/shave")
	 *
	 * @return string
	 */
	public function shave() {
		return 'redirect: http://dollar-shave-club.7eer.net/c/72409/74122/1969';
	}

	/**
	 * @Route ("/ting")
	 *
	 * @return string
	 */
	public function ting() {
		return 'redirect: http://ting.7eer.net/c/72409/87559/2020';
	}

	/**
	 * @Route ("/amazon")
	 *
	 * @return string
	 */
	public function amazon() {
		return 'redirect: http://www.amazon.com/?tag=des000-20';
	}

}
