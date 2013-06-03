<?php
namespace Destiny\Action;

use Destiny\Service\Twitch;
use Destiny\Utils\Http;

class Stream {

	public function execute(array $params) {
		$response = Twitch::getInstance ()->getStreamInfo ();
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $response->getCache()->getLastModified() ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, $response->contentType );
		Http::sendString ( $response );
	}

}
