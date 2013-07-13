<?php
namespace Destiny\Action\Help;

use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Agreement {

	/**
	 * @Route ("/help/agreement")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$model->title = 'User agreement';
		return 'help/agreement';
	}

}