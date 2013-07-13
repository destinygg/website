<?php
namespace Destiny\Action\Chat;

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
class Faq {

	/**
	 * @Route ("/chat/faq")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$model->title = 'Frequently Asked Questions';
		return 'chat/faq';
	}

}