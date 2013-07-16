<?php
namespace Destiny\Action\Web\Fantasy\Team;

use Destiny\Common\Service\Fantasy\ChampionService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

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