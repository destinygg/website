<?php
namespace Destiny\Action;

use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Chat {

	/**
	 * @Route ("/chat")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		return 'chat';
	}

}
