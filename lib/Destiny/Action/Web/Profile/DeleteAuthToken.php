<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\Exception;
use Destiny\Authentication\Service\ApiAuthenticationService;
use Destiny\Common\Session;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class DeleteAuthToken {

	/**
	 * @Route ("/profile/authtoken/{authToken}/delete")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		if (! isset ( $params ['authToken'] ) || empty ( $params ['authToken'] )) {
			throw new Exception ( 'Invalid auth token' );
		}
		$userId = Session::getCredentials ()->getUserId ();
		$apiAuthService = ApiAuthenticationService::instance ();
		$authToken = $apiAuthService->getAuthToken ( $params ['authToken'] );
		if (empty ( $authToken )) {
			throw new Exception ( 'Auth token not found' );
		}
		if ($authToken ['userId'] != $userId) {
			throw new Exception ( 'Auth token not owned by user' );
		}
		$apiAuthService->removeAuthToken ( $authToken ['authTokenId'] );
		return 'redirect: /profile/authentication';
	}

}