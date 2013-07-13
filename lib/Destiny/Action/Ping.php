<?php
namespace Destiny\Action;

use Destiny\Utils\Http;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

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
		Http::header ( 'X-Ping', 'Destiny' );
		Http::status ( Http::STATUS_OK );
		exit ();
	}

}