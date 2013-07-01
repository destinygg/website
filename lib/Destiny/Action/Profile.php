<?php
namespace Destiny\Action;

use Destiny\Service\AuthenticationService;
use Destiny\Service\UserFeaturesService;
use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\AppException;
use Destiny\Utils\Country;
use Destiny\ViewModel;
use Destiny\UserFeature;

class Profile {

	public function executeGet(array $params, ViewModel $model) {
		$userService = UserService::instance ();
		$model->title = 'Profile';
		$model->user = $userService->getUserById ( Session::get ( 'userId' ) );
		return 'profile';
	}

	public function executePost(array $params, ViewModel $model) {
		// Get user
		$userService = UserService::instance ();
		$userFeaturesService = UserFeaturesService::instance ();
		$user = $userService->getUserById ( Session::get ( 'userId' ) );
		if (empty ( $user )) {
			throw new AppException ( 'Invalid user' );
		}
		
		$username = (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) ? $params ['username'] : '';
		$email = (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) ? $params ['email'] : '';
		$country = (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) ? $params ['country'] : '';
		
		try {
			AuthenticationService::instance ()->validateUsername ( $username, $user );
			AuthenticationService::instance ()->validateEmail ( $email, $user );
			if (! empty ( $country )) {
				$countryArr = Country::getCountryByCode ( $country );
				if (empty ( $countryArr )) {
					throw new AppException ( 'Invalid country' );
				}
				$country = $countryArr ['alpha-2'];
			}
		} catch ( AppException $e ) {
			$model->title = 'Profile';
			$model->user = $user;
			$model->error = $e;
			return 'profile';
		}
		
		// Preferences
		if (isset ( $params ['feature'] ) && ! empty ( $params ['feature'] )) {
			$userFeatures = Session::getCredentials ()->getFeatures ();
			if (isset ( $params ['feature'] [UserFeature::STICKY_TEAMBAR] ) && $params ['feature'] [UserFeature::STICKY_TEAMBAR] == 1) {
				if (isset ( $userFeatures [UserFeature::STICKY_TEAMBAR] ) && $userFeatures [UserFeature::STICKY_TEAMBAR] == 0) {
					$userFeaturesService->addUserFeature ( $user ['userId'], UserFeature::STICKY_TEAMBAR );
				}
			}
			if (isset ( $params ['feature'] [UserFeature::STICKY_TEAMBAR] ) && $params ['feature'] [UserFeature::STICKY_TEAMBAR] == 0) {
				if (isset ( $userFeatures [UserFeature::STICKY_TEAMBAR] ) && $userFeatures [UserFeature::STICKY_TEAMBAR] == 1) {
					$userFeaturesService->removeUserFeature ( $user ['userId'], UserFeature::STICKY_TEAMBAR );
				}
			}
		}
		
		// Update user
		$userService->updateUser ( $user ['userId'], array (
			'username' => $username,
			'country' => $country,
			'email' => $email 
		) );
		
		// Update authentication credentials
		$credentials = Session::getCredentials ();
		$credentials->setCountry ( $user ['country'] );
		$credentials->setEmail ( $email );
		$credentials->setUsername ( $username );
		$credentials->setFeatures ( $userFeaturesService->getUserFeatures ( $user ['userId'] ) );
		Session::updateCredentials ( $credentials );
		
		$model->title = 'Profile';
		$model->user = $userService->getUserById ( Session::get ( 'userId' ) );
		$model->profileUpdated = true;
		return 'profile';
	}

}
