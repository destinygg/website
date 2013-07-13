<?php
namespace Destiny\Action;

use Destiny\Application;
use Destiny\Config;
use Destiny\MimeType;
use Destiny\Utils\Http;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Summoners {

	/**
	 * @Route ("/summoners")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$summoners = $app->getCacheDriver ()->fetch ( 'summoners' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $summoners ) );
	}

}