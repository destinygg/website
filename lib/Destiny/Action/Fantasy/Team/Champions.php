<?php

namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\ChampionService;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Session;

class Champions {

	public function execute(array $params) {
		$response = array (
				'success' => false,
				'data' => array (),
				'message' => '' 
		);
		$response ['success'] = true;
		$response ['data'] = ChampionService::instance ()->getUserChampions ( Session::get ( 'userId' ) );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}