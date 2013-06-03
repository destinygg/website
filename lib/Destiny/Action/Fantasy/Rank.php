<?php
namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Rank {

	public function execute(array $params) {
		$response = array (
			'success' => false,
			'data' => array (),
			'message' => '' 
		);
		try {
			if ($params ['name']) {
				$name = $params ['name'];
			}
			$teamService = Team::getInstance ();
			$teams = $teamService->getTeamsByUsername ( $name );
			if (empty ( $teams )) {
				throw new \Exception ( 'No teams found' );
			}
			
			$response ['data'] = $teams;
			$response ['success'] = true;
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage ();
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}