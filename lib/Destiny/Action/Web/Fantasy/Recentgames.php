<?php
namespace Destiny\Action\Web\Fantasy;

use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Date;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

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
		
		if (! Http::checkIfModifiedSince ( $aggregateDate->getTimestamp () )) {
			$response = new HttpEntity ( Http::STATUS_NOT_MODIFIED );
			$response->addHeader ( Http::HEADER_LAST_MODIFIED, $aggregateDate->format ( 'r' ) );
			$response->addHeader ( Http::HEADER_CONNECTION, 'close' );
			return $response;
		}
		
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( array (
			'date' => $aggregateDate->format ( Date::FORMAT ) 
		) ) );
		$response->addHeader ( Http::HEADER_LAST_MODIFIED, $aggregateDate->format ( 'r' ) );
		$response->addHeader ( Http::HEADER_CACHE_CONTROL, 'private' );
		$response->addHeader ( Http::HEADER_PRAGMA, 'public' );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}