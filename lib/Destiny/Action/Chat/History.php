<?php
namespace Destiny\Action\Chat;

use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\ViewModel;
use Destiny\Application;
use Destiny\Session;
use Destiny\MimeType;
use Destiny\Config;
use Destiny\Service\ChatlogService;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

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
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
		Http::sendString ( 'var backlog = ' . json_encode ( $chatlog ) );
		exit ();
	}

}
