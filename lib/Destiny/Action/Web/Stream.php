<?php
namespace Destiny\Action\Web;

use Destiny\Common\AppException;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\MimeType;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
