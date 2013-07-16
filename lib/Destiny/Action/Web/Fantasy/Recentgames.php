<?php
namespace Destiny\Action\Web\Fantasy;

use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Date;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Recentgames {

	/**
	 * @Route ("/fantasy/recentgames")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$response = null;
		$game = GameService::instance ()->getRecentGameData ();
		$aggregateDate = Date::getDateTime ( $game ['aggregatedDate'] );
		Http::checkIfModifiedSince ( $aggregateDate->getTimestamp (), true );
		Http::header ( Http::HEADER_LAST_MODIFIED, $aggregateDate->format ( 'r' ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( array (
			'date' => $aggregateDate->format ( Date::FORMAT ) 
		) ) );
	}

}