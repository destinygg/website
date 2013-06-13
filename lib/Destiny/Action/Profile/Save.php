<?php

namespace Destiny\Action\Profile;

use Destiny\Session;
use Destiny\Utils\Country;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Service\Fantasy\Db\User;
use Destiny\Service\Settings;
use Destiny\Service\UsersService;
use Destiny\ViewModel;
use Destiny\AppException;

class Save {

	public function execute(array $params) {
		// Get user
		$user = UsersService::getInstance ()->getUserById ( Session::get ( 'userId' ) );
		if (empty ( $user )) {
			throw new AppException ( 'Invalid user' );
		}
		
		// Geo
		if (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) {
			$country = Country::getCountryByCode ( $params ['country'] );
			if (! empty ( $country )) {
				$user ['country'] = $country['alpha-2'];
			}
		}
		
		// Preferences
		if (isset ( $params ['teambar_homepage'] )) {
			Settings::set ( 'teambar_homepage', intval ( $params ['teambar_homepage'] ) );
		}
		
		// Update authentication credentials
		$authCreds = Session::getAuthCreds ();
		$authCreds->setCountry ( $user ['country'] );
		Session::setAuthCreds ( $authCreds );
		
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( array (
				'success' => true 
		) ) );
	}

}