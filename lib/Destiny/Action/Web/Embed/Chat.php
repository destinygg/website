<?php
namespace Destiny\Action\Web\Embed;

use Destiny\Common\UserRole;
use Destiny\Common\ViewModel;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

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
		$emotes = Config::$a ['chat'] ['customemotes'];
		natcasesort( $emotes );
		return array (
			'port' => Config::$a ['chat'] ['port'],
			'maxlines' => Config::$a ['chat'] ['maxlines'],
			'emoticons' => array_values( $emotes ),
		);
	}

}
