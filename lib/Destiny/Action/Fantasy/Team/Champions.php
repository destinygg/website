<?php

namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;

class Champions {

	public function execute(array $params) {
		$response = array (
				'success' => false,
				'data' => array (),
				'message' => '' 
		);
		try {
			if (! Session::authorized ()) {
				throw new \Exception ( 'User required' );
			}
			$response ['success'] = true;
			$response ['data'] = Champion::getInstance ()->getUserChampions ( Session::get ( 'userId' ) );
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage ();
		}
		
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}