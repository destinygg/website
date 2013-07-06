<?php

namespace Destiny\Action\Chat;

use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;

class Faq {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'Frequently Asked Questions';
		return 'chat/faq';
	}

}