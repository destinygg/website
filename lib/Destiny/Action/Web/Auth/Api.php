<?php
namespace Destiny\Action\Web\Auth;

use Destiny\Common\MimeType;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\Service\UserFeaturesService;
use Destiny\Common\UserRole;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Service\ApiAuthenticationService;
use Destiny\Common\ViewModel;
use Destiny\Common\Utils\Http;
use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\AppException;
use Destiny\Common\Config;
use Destiny\Common\OAuthClient;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\Service\UserService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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
	 * @throws AppException
	 */
	public function execute(array $params, ViewModel $model) {
		$app = Application::instance ();
		$response = null;
		try {
			if (! isset ( $params ['authtoken'] ) || empty ( $params ['authtoken'] )) {
				Http::status ( Http::STATUS_FORBIDDEN );
				die ( 'Invalid or empty authToken' );
			}
			$authToken = ApiAuthenticationService::instance ()->getAuthToken ( $params ['authtoken'] );
			if (empty ( $authToken )) {
				Http::status ( Http::STATUS_FORBIDDEN );
				die ( 'Auth token not found' );
			}
			$user = UserService::instance ()->getUserById ( $authToken ['userId'] );
			if (empty ( $user )) {
				Http::status ( Http::STATUS_FORBIDDEN );
				die ( 'User not found' );
			}
			$credentials = new SessionCredentials ( $user );
			$credentials->setAuthProvider ( 'API' );
			$credentials->addRoles ( UserRole::USER );
			$credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $authToken ['userId'] ) );
			$credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $authToken ['userId'] ) );
			$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $authToken ['userId'] );
			if (! empty ( $subscription )) {
				$credentials->addRoles ( UserRole::SUBSCRIBER );
				$credentials->addFeatures ( \Destiny\Common\UserFeature::SUBSCRIBER );
				if ($subscription ['subscriptionTier'] == 1) {
					$credentials->addFeatures ( \Destiny\Common\UserFeature::SUBSCRIBERT1 );
				} else if ($subscription ['subscriptionTier'] == 2) {
					$credentials->addFeatures ( \Destiny\Common\UserFeature::SUBSCRIBERT2 );
				}
			}
			$response = $credentials->getData ();
			Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
			Http::sendString ( json_encode ( $response ) );
		} catch ( \Exception $e ) {
			Http::status ( Http::STATUS_ERROR );
			die ( $e->getMessage () );
		}
	}

}