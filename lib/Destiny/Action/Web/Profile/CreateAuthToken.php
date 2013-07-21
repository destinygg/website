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
class CreateAuthToken {

	/**
	 * @Route ("/profile/authtoken/create")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
	public function execute(array $params) {
		$apiAuthService = ApiAuthenticationService::instance ();
		$userId = Session::getCredentials ()->getUserId ();
		$tokens = $apiAuthService->getAuthTokensByUserId ( $userId );
		if (count ( $tokens ) >= 5) {
			throw new AppException ( 'You have reached the maximum [5] allowed login keys.' );
		}
		$token = $apiAuthService->createAuthToken ( $userId );
		$apiAuthService->addAuthToken ( $userId, $token );
		Http::header ( Http::HEADER_LOCATION, '/profile/authentication' );
		die ();
	}

}