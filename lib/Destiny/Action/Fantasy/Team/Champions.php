<?php
namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\ChampionService;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\Session;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Champions {

	/**
	 * @Route ("/fantasy/team/champions")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 */
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