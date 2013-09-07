<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\Session;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\HttpEntity;

/**
 * @Action
 */
class Info {

	/**
	 * @Route ("/profile/info")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( Session::getCredentials ()->getData () ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}