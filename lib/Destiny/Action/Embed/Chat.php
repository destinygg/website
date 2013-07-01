<?php
namespace Destiny\Action\Embed;

use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\Service\Fantasy\TeamService;
use Destiny\Config;
use Destiny\Service\ChatlogService;

class Chat {

	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$model->chatOptions = $this->getChatOptionParams ( $params );
		$user = Session::getCredentials ()->getData ();
		if ($user ['userId']) $model->user = array (
			'nick' => $user ['username'],
			'features' => $user ['features'] 
		);
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
			$params ['theme'] = Config::$a ['chat'] ['defaultTheme'];
		}
		$options ['theme'] = $params ['theme'];
		$options ['maxlines'] = Config::$a ['chat'] ['maxlines'];
		return $options;
	}

}
