<?php
namespace Destiny\Action\Profile;

use Destiny\Session;
use Destiny\Utils\Country;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Service\Fantasy\Db\User;
use Destiny\Service\Settings;

class Save {

	public function execute(array $params = array()) {
		if ($_SERVER ['REQUEST_METHOD'] != 'POST') {
			throw new \Exception ( 'POST required' );
		}
		if (! Session::getAuthorized ()) {
			throw new \Exception ( 'User required' );
		}
		$user = &Session::getUser ();
		if (! isset ( $user ['country'] )) {
			$country = Country::getCountryByCode ( $params ['country'] );
			$user ['country'] = (! empty ( $country )) ? $country->{'alpha-2'} : '';
			User::getInstance ()->updateUser ( $user );
		}
		if (! isset ( $user ['teambar_homepage'] )) {
			Settings::getInstance ()->set ( 'teambar_homepage', intval ( $params ['teambar_homepage'] ) );
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( array (
				'success' => true
		) ) );
	}

}