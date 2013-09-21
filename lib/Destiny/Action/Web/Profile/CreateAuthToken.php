<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
use Destiny\Authentication\Service\ApiAuthenticationService;

/**
 * @Action
 */
class CreateAuthToken {

	/**
	 * @Route ("/profile/authtoken/create")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$apiAuthService = ApiAuthenticationService::instance ();
		$userId = Session::getCredentials ()->getUserId ();
		$tokens = $apiAuthService->getAuthTokensByUserId ( $userId );
		if (count ( $tokens ) >= 5) {
			throw new Exception ( 'You have reached the maximum [5] allowed login keys.' );
		}
		$token = $apiAuthService->createAuthToken ( $userId );
		$apiAuthService->addAuthToken ( $userId, $token );
		return 'redirect: /profile/authentication';
	}

}