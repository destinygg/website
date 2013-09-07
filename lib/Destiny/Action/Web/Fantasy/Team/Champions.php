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
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Champions {

	/**
	 * @Route ("/fantasy/team/champions")
	 * @Secure ({"USER"})
	 * @Transactional
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
		$response ['data'] = ChampionService::instance ()->getUserChampions ( Session::getCredentials()->getUserId() );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}