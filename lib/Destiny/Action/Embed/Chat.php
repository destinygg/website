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
		$model->user = Session::getCredentials ()->getData ();
		$model->chatOptions = $this->getChatOptionParams ( $params );
		return 'embed/chat';
	}

	/**
	 * Get the chat params from the get request
	 * Make sure they are all valid
	 *
	 * @param array $params
	 */
	private function getChatOptionParams(array $params) {
		$options = array ();
		if (! isset ( $params ['theme'] ) || empty ( $params ['theme'] ) || $params ['theme'] != 'light' && $params ['theme'] != 'dark') {
			$params ['theme'] = 'light';
		}
		$options ['theme'] = $params ['theme'];
		return $options;
	}

}
