<?php
namespace Destiny\Action;

use Destiny\ViewModel;
use Destiny\Application;

class Chat {

	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		return 'chat';
	}

}
