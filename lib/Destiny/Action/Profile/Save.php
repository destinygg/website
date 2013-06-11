<?php

namespace Destiny\Action\Profile;

use Destiny\Session;
use Destiny\Utils\Country;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Service\Fantasy\Db\User;
use Destiny\Service\Settings;
use Destiny\Service\Users;
use Destiny\ViewModel;

class Save {

	public function execute(array $params) {
		if ($_SERVER ['REQUEST_METHOD'] != 'POST') {
			throw new \Exception ( 'POST required' );
		}
		
		// Get user
		$user = Users::getInstance ()->getUserById ( Session::get ( 'userId' ) );
		if (empty ( $user )) {
			throw new \Exception ( 'Invalid user' );
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
		$authCreds = Session::getAuthCredentials ();
		$authCreds->setCountry ( $user ['country'] );
		Session::setAuthCredentials ( $authCreds );
		
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( array (
				'success' => true 
		) ) );
	}

}