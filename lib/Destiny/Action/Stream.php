<?php
namespace Destiny\Action;

use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\MimeType;
use Destiny\Config;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Stream {

	/**
	 * @Route ("/stream")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$app = Application::instance ();
		$info = $app->getCacheDriver ()->fetch ( 'streaminfo' );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $info ) );
	}

}
