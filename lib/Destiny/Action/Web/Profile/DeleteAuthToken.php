<?php
namespace Destiny\Action\Web\Profile;

use Destiny\Common\AppException;
use Destiny\Common\Service\ApiAuthenticationService;
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
class DeleteAuthToken {

	/**
	 * @Route ("/profile/authtoken/{authToken}/delete")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		if (! isset ( $params ['authToken'] ) || empty ( $params ['authToken'] )) {
			throw new AppException ( 'Invalid auth token' );
		}
		$userId = Session::getCredentials ()->getUserId ();
		$apiAuthService = ApiAuthenticationService::instance ();
		$authToken = $apiAuthService->getAuthToken ( $params ['authToken'] );
		if (empty ( $authToken )) {
			throw new AppException ( 'Auth token not found' );
		}
		if ($authToken ['userId'] != $userId) {
			throw new AppException ( 'Auth token not owned by user' );
		}
		$apiAuthService->removeAuthToken ( $authToken ['authTokenId'] );
		return 'redirect: /profile/authentication';
	}

}