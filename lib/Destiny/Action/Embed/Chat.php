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
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Chat {

	/**
	 * @Route ("/embed/chat")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function execute(array $params, ViewModel $model) {
		$user = null;
		if (Session::hasRole ( UserRole::USER )) {
			$creds = Session::getCredentials ();
			$user = array ();
			$user ['username'] = $creds->getUsername ();
			$user ['features'] = $creds->getFeatures ();
		}
		$model->options = $this->getChatOptionParams ( $params );
		$model->user = $user;
		return 'embed/chat';
	}

	/**
	 * Get the chat params from the get request
	 * Make sure they are all valid
	 *
	 * @param array $params
	 */
	private function getChatOptionParams(array $params) {
		return array (
			'maxlines' => Config::$a ['chat'] ['maxlines'] 
		);
	}

}
