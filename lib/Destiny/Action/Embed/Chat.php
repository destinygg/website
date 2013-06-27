<?php
namespace Destiny\Action\Embed;

use Destiny\AppException;

use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;

class Chat {

	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$model->user = Session::getCredentials()->getData();
		return 'embed/chat';
	}

}
