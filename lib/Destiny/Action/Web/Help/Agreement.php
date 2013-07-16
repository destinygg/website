<?php
namespace Destiny\Action\Web\Help;

use Destiny\Common\Service\UserService;
use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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