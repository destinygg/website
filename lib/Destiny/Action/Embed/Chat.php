<?php
namespace Destiny\Action\Embed;

use Destiny\UserRole;
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
		
		$chatLog = ChatlogService::instance ()->getChatLog ( Config::$a ['chat'] ['backlog'] );
		$log = array ();
		foreach ( $chatLog as $i => $line ) {
			$log [] = $line;
		}
		$model->backlog = $log;
		
		if (Session::hasRole ( UserRole::USER )) {
			$user = Session::getCredentials ()->getData ();
			$model->user = array (
				'username' => $user ['username'],
				'features' => $user ['features'],
				'color' => $user ['color'] 
			);
		}
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
