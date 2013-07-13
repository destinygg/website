<?php
namespace Destiny\Action\Profile;

use Destiny\Session;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

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
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( Session::getCredentials ()->getData () ) );
	}

}