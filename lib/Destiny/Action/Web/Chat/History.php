<?php
namespace Destiny\Action\Web\Chat;

use Destiny\Common\UserFeature;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use Destiny\Common\ViewModel;
use Destiny\Common\Application;
use Destiny\Common\Session;
use Destiny\Common\MimeType;
use Destiny\Common\Config;
use Destiny\Common\Service\ChatlogService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class History {

	/**
	 * @Route ("/chat/history")
	 *
	 * @param array $params
	 * @param ViewModel $model
	 */
	public function execute(array $params, ViewModel $model) {
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
			}
			$lines [] = $line;
		}
		
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
		Http::sendString ( 'var backlog = ' . json_encode ( $lines ) );
		exit ();
	}

}
