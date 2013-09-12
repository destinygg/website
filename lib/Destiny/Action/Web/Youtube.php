<?php
namespace Destiny\Action\Web;

use Destiny\Common\HttpEntity;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

/**
 * @Action
 */
class Youtube {

	/**
	 * @Route ("/youtube")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$playlist = $app->getCacheDriver ()->fetch ( 'youtubeplaylist' );
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $playlist ) );
		$response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
		$response->addHeader ( Http::HEADER_PRAGMA, 'public' );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}