<?php
namespace Destiny\Action\Admin\User;

use Destiny\Common\HttpEntity;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\User\Service\UserService;

/**
 * @Action
 */
class Find {

	/**
	 * @Route ("/admin/user/find")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$s = (isset ( $params ['username'] )) ? $params ['username'] : '';
		if (! isset ( $params ['exact'] )) {
			$s .= '%';
		}
		$users = UserService::instance ()->findUsersByUsername ( $s, 10 );
		$response = new HttpEntity ( Http::STATUS_OK );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		$response->setBody ( json_encode ( $users ) );
		return $response;
	}

}