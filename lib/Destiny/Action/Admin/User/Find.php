<?php
namespace Destiny\Action\Admin\User;

use Destiny\Service\UserService;
use Destiny\AppException;
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
class Find {

	/**
	 * @Route ("/admin/user/find")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$s = $params ['username'];
		if (! isset ( $params ['exact'] )) {
			$s .= '%';
		}
		$users = UserService::instance ()->findUsersByUsername ( $s );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $users ) );
		exit ();
	}

}
	