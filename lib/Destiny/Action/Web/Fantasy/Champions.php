<?php
namespace Destiny\Action\Web\Fantasy;

use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Application;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;

/**
 * @Action
 */
class Champions {

	/**
	 * @Route ("/fantasy/champions")
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$champions = Application::instance ()->getCacheDriver ()->fetch ( 'champions' );
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $champions ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}