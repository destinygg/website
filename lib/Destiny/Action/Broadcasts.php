<?php
namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Application;
use Destiny\Config;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Broadcasts {

	/**
	 * @Route ("/broadcasts")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$broadcasts = $app->getCacheDriver ()->fetch ( 'pastbroadcasts' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $broadcasts ) );
	}

}