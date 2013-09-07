<?php
namespace Destiny\Action\Web;

use Destiny\Common\HttpEntity;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $summoners ) );
		$response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
		$response->addHeader ( Http::HEADER_PRAGMA, 'public' );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}