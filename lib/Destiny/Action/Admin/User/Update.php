<?php
namespace Destiny\Action\Admin\User;

use Destiny\Service\UserFeaturesService;
use Destiny\AppException;
use Destiny\Service\UserService;
use Destiny\Session;
use Destiny\ViewModel;
use Destiny\Service\AuthenticationService;
use Destiny\Service\Fantasy\GameService;
use Destiny\Utils\Country;
use Destiny\Utils\Http;

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
		if (isset ( $params ['features'] ) && is_array ( $params ['features'] )) {
			$features = UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] );
			UserFeaturesService::instance ()->setUserFeatures ( $user ['userId'], array_unique ( array_merge ( $features, $params ['features'] ) ) );
		}
		
		// Lazy
		Http::header ( Http::HEADER_LOCATION, sprintf ( '/admin/user/%s', $user ['userId'] ) );
		die ();
		
		// @TODO need to update redis var
	}

}
