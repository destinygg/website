<?php
namespace Destiny\Action;

use Destiny\Utils\Mimetype;
use Destiny\Utils\Http;

class Twitter {

	public function execute(array $params) {
		$response = \Destiny\Service\Twitter::getInstance()->getTimeline();
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $response->getCache()->getLastModified() ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, $response->contentType );
		Http::sendString ( $response );
	}

}