<?php
namespace Destiny\Action\Web\Chat;

use Destiny\Common\AppException;
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
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JAVASCRIPT );
		Http::sendString ( 'var backlog = ' . json_encode ( $chatlog ) );
		exit ();
	}

}
