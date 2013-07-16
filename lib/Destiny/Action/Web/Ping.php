<?php
namespace Destiny\Action\Web;

use Destiny\Common\Utils\Http;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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