<?php
namespace Destiny\Action\Web;

use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

/**
 * @Action
 */
class Ping {

	/**
	 * @Route ("/ping")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$response = new HttpEntity ( Http::STATUS_OK );
		$response->addHeader ( 'X-Pong', 'Destiny' );
		return $response;
	}

}