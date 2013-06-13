<?php

namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\ChampionService;
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
		$response ['success'] = true;
		$response ['data'] = ChampionService::getInstance ()->getUserChampions ( Session::get ( 'userId' ) );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}