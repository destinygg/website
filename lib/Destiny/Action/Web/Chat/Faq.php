<?php
namespace Destiny\Action\Web\Chat;

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