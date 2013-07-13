<?php
namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Application;
use Destiny\Config;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Champions {

	/**
	 * @Route ("/fantasy/champions")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$champions = Application::instance ()->getCacheDriver ()->fetch ( 'champions' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $champions ) );
	}

}