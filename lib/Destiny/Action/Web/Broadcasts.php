<?php
namespace Destiny\Action\Web;

use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\HttpEntity;

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
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $broadcasts ) );
		$response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
		$response->addHeader ( Http::HEADER_PRAGMA, 'public' );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}