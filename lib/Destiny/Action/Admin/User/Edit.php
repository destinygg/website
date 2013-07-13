<?php
namespace Destiny\Action\Admin\User;

use Destiny\Service\UserFeaturesService;
use Destiny\AppException;
use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\SessionCredentials;
use Destiny\ViewModel;
use Destiny\UserRole;
use Destiny\Utils\Country;
use Destiny\Service\ChatIntegrationService;
use Destiny\Service\AuthenticationService;
use Destiny\Service\SubscriptionsService;
use Destiny\Service\Fantasy\GameService;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Edit {

	/**
	 * @Route ("/admin/user")
	 * @Route ("/admin/user/{id}/edit")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"GET"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function executeGet(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		$user ['roles'] = UserService::instance ()->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getFeatures ();
		return 'admin/user';
	}

	/**
	 * @Route ("/admin/user")
	 * @Route ("/admin/user/{id}/edit")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"POST"})
	 *
	 * @param array $params
	 * @param ViewModel $model
	 * @throws AppException
	 * @return string
	 */
	public function executePost(array $params, ViewModel $model) {
		$model->title = 'User';
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'userId required' );
		}
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		if (empty ( $user )) {
			throw new AppException ( 'User was not found' );
		}
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : $user ['username'];
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : $user ['email'];
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : $user ['country'];
		
		AuthenticationService::instance ()->validateUsername ( $username, $user );
		AuthenticationService::instance ()->validateEmail ( $email, $user );
		if (! empty ( $country )) {
			$countryArr = Country::getCountryByCode ( $country );
			if (empty ( $countryArr )) {
				throw new AppException ( 'Invalid country' );
			}
			$country = $countryArr ['alpha-2'];
		}
		
		// Data for update
		$userData = array (
			'username' => $username,
			'country' => $country,
			'email' => $email 
		);
		UserService::instance ()->updateUser ( $user ['userId'], $userData );
		$user = UserService::instance ()->getUserById ( $params ['id'] );
		
		// Features
		if (! isset ( $params ['features'] )) $params ['features'] = array ();
		UserFeaturesService::instance ()->setUserFeatures ( $user ['userId'], $params ['features'] );
		
		// Roles
		if (! isset ( $params ['roles'] )) $params ['roles'] = array ();
		UserService::instance ()->setUserRoles ( $user ['userId'], $params ['roles'] );
		
		// Update the users credentials - this still requires the user to login/out
		$credentials = new SessionCredentials ( $user );
		$credentials->setAuthProvider ( '' ); // we need to get the auth provider
		$credentials->addRoles ( UserRole::USER );
		$credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] ) );
		$credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $user ['userId'] ) );
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
		if (! empty ( $subscription )) {
			$credentials->addRoles ( UserRole::SUBSCRIBER );
			$credentials->addFeatures ( \Destiny\UserFeature::SUBSCRIBER );
		}
		
		// Update the auth credentials
		ChatIntegrationService::instance ()->refreshUser ( $credentials );
		
		$user ['roles'] = UserService::instance ()->getUserRolesByUserId ( $user ['userId'] );
		$user ['features'] = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
		$model->user = $user;
		$model->features = UserFeaturesService::instance ()->getFeatures ();
		$model->profileUpdated = true;
		return 'admin/user';
	}

}
