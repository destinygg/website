<?php
namespace Destiny\Action\Admin\User;

use Destiny\Common\Service\UserService;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $users ) );
		exit ();
	}

}