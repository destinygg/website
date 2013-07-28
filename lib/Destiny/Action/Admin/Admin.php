<?php
namespace Destiny\Action\Admin;

use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		return 'admin';
	}

}
