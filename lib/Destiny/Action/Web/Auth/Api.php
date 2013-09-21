<?php
namespace Destiny\Action\Web\Auth;

use Destiny\Common\HttpEntity;
use Destiny\Common\MimeType;
use Destiny\Common\SessionCredentials;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Http;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\User\UserRole;
use Destiny\User\UserFeature;
use Destiny\User\Service\UserService;
use Destiny\User\Service\UserFeaturesService;
use Destiny\Authentication\Service\ApiAuthenticationService;
use Destiny\Commerce\Service\SubscriptionsService;

/**
 * @Action
 */
class Api {
	
	/**
	 * The current auth type
	 *
	 * @var string
	 */
	protected $authProvider = 'API';

	/**
	 * @Route ("/auth/api")
	 *
	 * Handle the incoming oAuth request
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$response = null;
		try {
			if (! isset ( $params ['authtoken'] ) || empty ( $params ['authtoken'] )) {
				return new HttpEntity ( Http::STATUS_FORBIDDEN, 'Invalid or empty authToken' );
			}
			$authToken = ApiAuthenticationService::instance ()->getAuthToken ( $params ['authtoken'] );
			if (empty ( $authToken )) {
				return new HttpEntity ( Http::STATUS_FORBIDDEN, 'Auth token not found' );
			}
			$user = UserService::instance ()->getUserById ( $authToken ['userId'] );
			if (empty ( $user )) {
				return new HttpEntity ( Http::STATUS_FORBIDDEN, 'User not found' );
			}
			$credentials = new SessionCredentials ( $user );
			$credentials->setAuthProvider ( 'API' );
			$credentials->addRoles ( UserRole::USER );
			$credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $authToken ['userId'] ) );
			$credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $authToken ['userId'] ) );
			$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $authToken ['userId'] );
			if (! empty ( $subscription )) {
				$credentials->addRoles ( UserRole::SUBSCRIBER );
				$credentials->addFeatures ( UserFeature::SUBSCRIBER );
				if ($subscription ['subscriptionTier'] == 2) {
					$credentials->addFeatures ( UserFeature::SUBSCRIBERT2 );
				}
			}
			
			$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $credentials->getData () ) );
			$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
			return $response;
		} catch ( \Exception $e ) {
			$response = new HttpEntity ( Http::STATUS_ERROR, $e->getMessage () );
			return $response;
		}
	}

}