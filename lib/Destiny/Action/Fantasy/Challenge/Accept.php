<?php

namespace Destiny\Action\Fantasy\Challenge;

use Destiny\Service\Fantasy\Db\Challenge;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;

class Accept {

	public function execute(array $params) {
		if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
			throw new \Exception ( 'teamId required.' );
		}
		if (! Session::authorized ()) {
			throw new \Exception ( 'User required' );
		}
		$response = array (
				'success' => true,
				'data' => array (),
				'message' => '' 
		);
		if (intval ( $params ['teamId'] ) == intval ( Session::get ( 'teamId' ) )) {
			throw new \Exception ( 'Play with yourself?' );
		}
		$response ['response'] = Challenge::getInstance ()->acceptChallenge ( intval ( $params ['teamId'] ), intval ( Session::get ( 'teamId' ) ) );
		$response ['message'] = ($response ['response']) ? 'Accepted!' : 'Failed!';
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}
