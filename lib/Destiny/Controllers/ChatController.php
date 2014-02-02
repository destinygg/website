<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Chat\ChatlogService;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;

/**
 * @Controller
 */
class ChatController {

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
			'host' => Config::$a ['chat'] ['host'],
			'port' => Config::$a ['chat'] ['port'],
			'maxlines' => Config::$a ['chat'] ['maxlines'],
			'emoticons' => array_values( $emotes ),
		);
	}

	/**
	 * @Route ("/embed/chat")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function embedChat(array $params, ViewModel $model) {
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
	 * @Route ("/chat/faq")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function faq(array $params, ViewModel $model) {
		$model->title = 'Frequently Asked Questions';
		return 'chat/faq';
	}

	/**
	 * @Route ("/chat/history")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 */
	public function history(array $params, ViewModel $model) {
		$chatlog = ChatlogService::instance ()->getChatLog ( Config::$a ['chat'] ['backlog'] );
		$lines = array ();
		$suppress = array ();
		foreach ( $chatlog as &$line ) {
			
			if ($line ['event'] == 'MUTE' or $line ['event'] == 'BAN') {
				$suppress [$line ['target']] = true;
			}
			
			if (isset ( $suppress [$line ['username']] )) {
				continue;
			}
			
			if (! empty ( $line ['features'] )) {
				$line ['features'] = explode ( ',', $line ['features'] );
			} else {
				$line ['features'] = array ();
			}
			
			if (! empty ( $line ['subscriber'] ) && $line ['subscriber'] == 1) {
				$line ['features'] [] = UserFeature::SUBSCRIBER;
				if ($line ['subscriptionTier'] == 2) {
					$line ['features'] [] = UserFeature::SUBSCRIBERT2;
				}
				if ($line ['subscriptionTier'] == 3) {
					$line ['features'] [] = UserFeature::SUBSCRIBERT3;
				}
			}
			$lines [] = $line;
		}
		
		$response = new HttpEntity ( Http::STATUS_OK, 'var backlog = ' . json_encode ( $lines ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
		return $response;
	}

	/**
	 * @Route ("/chat/emotes.json")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 */
	public function emotes(array $params, ViewModel $model) {
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( Config::$a ['chat'] ['customemotes'] ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}
