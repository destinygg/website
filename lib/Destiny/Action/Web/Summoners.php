<?php
namespace Destiny\Action\Web;

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
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $summoners ) );
	}

}