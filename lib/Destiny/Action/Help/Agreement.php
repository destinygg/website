<?php

namespace Destiny\Action\Help;

use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;

class Agreement {

	public function execute(array $params, ViewModel $model) {
		$model->title = 'User agreement';
		return 'help/agreement';
	}

}