<?php
namespace Destiny\Action\Fantasy;

use Destiny\Application;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Config;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Servers {

	/**
	 * @Route ("/fantasy/servers")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$stats = $app->getCacheDriver ()->fetch ( 'leaguestatus' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $stats ) );
	}

}