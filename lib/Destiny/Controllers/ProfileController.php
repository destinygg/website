<?php
namespace Destiny\Controllers;

use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Country;
use Destiny\Common\ViewModel;
use Destiny\Common\Config;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Common\User\UserService;
use Destiny\Commerce\OrdersService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Api\ApiAuthenticationService;
use Destiny\Common\HttpEntity;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;

/**
 * @Controller
 */
class ProfileController {
	
	/**
	 * Get a subscriptions payment profile
	 * @TODO clean up
	 * @param array $subscription
	 * @return array
	 */
	private function getPaymentProfile(array $subscription){
		$orderService = OrdersService::instance ();
		$paymentProfile = null;
		if (! empty ( $subscription ) && ! empty ( $subscription ['paymentProfileId'] )) {
			$paymentProfile = $orderService->getPaymentProfileById ( $subscription ['paymentProfileId'] );
			if (! empty ( $paymentProfile )) {
				$paymentProfile ['billingCycle'] = $orderService->buildBillingCycleString ( $paymentProfile ['billingFrequency'], $paymentProfile ['billingPeriod'] );
			}
		}
		return $paymentProfile;
	}

	/**
	 * @Route ("/profile/info")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
	public function profileInfo(array $params) {
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( Session::getCredentials ()->getData () ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}
	
	/**
	 * @Route ("/profile")
	 * @HttpMethod ({"GET"})
	 * @Secure ({"USER"})
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @return string
	 */
	public function profile(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$orderService = OrdersService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
		
		$subscription = $subscriptionsService->getUserActiveSubscription ( Session::getCredentials ()->getUserId () );
		if (empty ( $subscription )) {
			$subscription = $subscriptionsService->getUserPendingSubscription ( Session::getCredentials ()->getUserId () );
		}
		
		$paymentProfile = null;
		$subscriptionType = null;
		if(!empty($subscription)){
			$subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
			$paymentProfile = $this->getPaymentProfile($subscription);
		}
		
		$model->title = 'Profile';
		$model->user = $userService->getUserById ( Session::getCredentials ()->getUserId () );
		$model->subscription = $subscription;
		$model->subscriptionType = $subscriptionType;
		$model->paymentProfile = $paymentProfile;
		return 'profile';
	}

	/**
	 * @Route ("/profile")
	 * @HttpMethod ({"POST"})
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws Exception
	 * @return string
	 */
	public function profileUpdate(array $params, ViewModel $model) {
		// Get user
		$userService = UserService::instance ();
		$userFeaturesService = UserFeaturesService::instance ();
		$authService = AuthenticationService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
		$authenticationService = AuthenticationService::instance ();
		
		$user = $userService->getUserById ( Session::getCredentials ()->getUserId () );
		if (empty ( $user )) {
			throw new Exception ( 'Invalid user' );
		}
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
		
		try {
			$authenticationService->validateUsername ( $username, $user );
			$authenticationService->validateEmail ( $email, $user );
			if (! empty ( $country )) {
				$countryArr = Country::getCountryByCode ( $country );
				if (empty ( $countryArr )) {
					throw new Exception ( 'Invalid country' );
				}
				$country = $countryArr ['alpha-2'];
			}
		} catch ( Exception $e ) {
			$model->title = 'Profile';
			$model->user = $user;
			$model->error = $e;
			return 'profile';
		}
		
		// Date for update
		$userData = array (
			'username' => $username,
			'country' => $country,
			'email' => $email 
		);
		
		// Is the user changing their name?
		if (strcasecmp ( $username, $user ['username'] ) !== 0) {
			$nameChangeCount = intval ( $user ['nameChangedCount'] );
			// have they hit their limit
			if ($nameChangeCount >= Config::$a ['profile'] ['nameChangeLimit']) {
				throw new Exception ( 'You have reached your name change limit' );
			} else {
				$userData ['nameChangedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
				$userData ['nameChangedCount'] = $nameChangeCount + 1;
			}
		}
		
		// Update user
		$userService->updateUser ( $user ['userId'], $userData );
		$authService->flagUserForUpdate ( $user ['userId'] );
		
		$subscription = $subscriptionsService->getUserActiveSubscription ( $user ['userId'] );
		if (empty ( $subscription )) {
			$subscription = $subscriptionsService->getUserPendingSubscription ( $user ['userId'] );
		}
		
		$paymentProfile = null;
		$subscriptionType = null;
		if(!empty($subscription)){
			$subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
			$paymentProfile = $this->getPaymentProfile($subscription);
		}
		
		$model->title = 'Profile';
		$model->user = $userService->getUserById ( $user ['userId'] );
		$model->subscription = $subscription;
		$model->subscriptionType = $subscriptionType;
		$model->paymentProfile = $paymentProfile;
		$model->profileUpdated = true;
		return 'profile';
	}

	/**
	 * @Route ("/profile/authentication")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @return string
	 */
	public function profileAuthentication(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$subscriptionsService = SubscriptionsService::instance ();
		$userId = Session::getCredentials ()->getUserId ();
		$model->title = 'Authentication';
		$model->user = $userService->getUserById ( $userId );
	
		// Build a list of profile types for UI purposes
		$authProfiles = $userService->getAuthProfilesByUserId ( $userId );
		$authProfileTypes = array ();
		if (! empty ( $authProfiles )) {
			foreach ( $authProfiles as $profile ) {
				$authProfileTypes [] = $profile ['authProvider'];
			}
			$model->authProfiles = $authProfiles;
			$model->authProfileTypes = $authProfileTypes;
		}
		
		$subscription = $subscriptionsService->getUserActiveSubscription ( $userId );
		if (empty ( $subscription )) {
			$subscription = $subscriptionsService->getUserPendingSubscription ( $userId );
		}
		$subscriptionType = null;
		if(!empty($subscription)){
			$subscriptionType = $subscriptionsService->getSubscriptionType ( $subscription ['subscriptionType'] );
		}

		$model->subscription = $subscription;
		$model->subscriptionType = $subscriptionType;
		$model->authTokens = ApiAuthenticationService::instance ()->getAuthTokensByUserId ( $userId );
		return 'profile/authentication';
	}

	/**
	 * @Route ("/profile/authtoken/create")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 */
	public function profileAuthtokenCreate(array $params) {
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

	/**
	 * @Route ("/profile/authtoken/{authToken}/delete")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 */
	public function profileAuthtokenDelete(array $params) {
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
