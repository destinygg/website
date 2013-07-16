<?php
namespace Destiny\Action\Web\Fantasy;

use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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