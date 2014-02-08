<?php
namespace Destiny\Controllers;

use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Chat\ChatlogService;
use Destiny\Common\Config;
use Destiny\Common\User\UserFeature;

/**
 * @Controller
 */
class ChatController {

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
		
		$response = new Response ( Http::STATUS_OK, 'var backlog = ' . json_encode ( $lines ) );
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
		$response = new Response ( Http::STATUS_OK, json_encode ( Config::$a ['chat'] ['customemotes'] ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}
