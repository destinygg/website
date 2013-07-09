<?php
namespace Destiny\Action\Admin\User;

use Destiny\Service\UserFeaturesService;
use Destiny\AppException;
use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\SessionCredentials;
use Destiny\ViewModel;
use Destiny\Service\AuthenticationService;
use Destiny\Service\Fantasy\GameService;
use Destiny\Utils\Country;
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\Service\SubscriptionsService;
use Destiny\UserRole;

class Update {

	public function execute(array $params, ViewModel $model) {
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

		// Features
		if (!isset ( $params ['features'] )){
			$params ['features'] = array();
		}
		UserFeaturesService::instance ()->setUserFeatures ( $user ['userId'], $params ['features'] );
		
		// Roles
		if (!isset ( $params ['roles'] )){
			$params ['roles'] = array();
		}
		UserService::instance ()->setUserRoles ( $user ['userId'], $params ['roles'] );
		
		// Update the users credentials - this still requires the user to login/out
		$credentials = new SessionCredentials ();
		$credentials->setUserId ( $user ['userId'] );
		$credentials->setUserName ( $user ['username'] );
		$credentials->setEmail ( $user ['email'] );
		$credentials->setCountry ( $user ['country'] );
		$credentials->setAuthProvider ( '' ); // we need to get the auth provider
		$credentials->setUserStatus ( $user ['userStatus'] );
		$credentials->addRoles ( UserRole::USER );
		$credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] ) );
		$credentials->addRoles ( UserService::instance ()->getUserRoles ( $user ['userId'] ) );
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
		if (! empty ( $subscription )) {
			$credentials->addRoles ( UserRole::SUBSCRIBER );
			$credentials->addFeatures ( \Destiny\UserFeature::SUBSCRIBER );
		}
		
		// Update the auth credentials
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->publish ( 'refreshuser', json_encode ( $credentials->getData () ) );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
		}
		
		// Lazy
		Http::header ( Http::HEADER_LOCATION, sprintf ( '/admin/user/%s', $user ['userId'] ) );
		die ();
	}

}
