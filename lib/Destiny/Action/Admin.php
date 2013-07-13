<?php
namespace Destiny\Action;

use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\Fantasy\GameService;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Admin {

	/**
	 * @Route ("/admin")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"GET","POST"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$model->title = 'Administration';
		$model->user = Session::getCredentials ()->getData ();
		$model->games = GameService::instance ()->getGames ( 10, 0 );
		$model->tracks = GameService::instance ()->getTrackedProgress ( 10, 0 );
		return 'admin';
	}

}
