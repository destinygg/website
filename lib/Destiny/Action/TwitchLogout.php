<?php
namespace Destiny\Action;

use Destiny\Session;
use Destiny\Utils\Http;

class TwitchLogout {

	public function execute(array $params) {
		Session::destroy ();
		Http::header ( 'Location', '/' );
	}
	
}