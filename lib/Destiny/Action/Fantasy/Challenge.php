<?php
namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Service\Fantasy\Db\Challenge;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Challenge {

	public function execute(array $params) {
		$response = array (
				'success' => true,
				'data' => array (),
				'message' => '' 
		);
		try {
			if (! isset ( $params ['name'] ) || empty ( $params ['name'] )) {
				throw new \Exception ( 'name required.' );
			}
			if (! Session::getAuthorized ()) {
				throw new \Exception ( 'User required' );
			}
			$teamService = Team::getInstance ();
			$teams = $teamService->getTeamsByUsername ( mysql_real_escape_string ( $params ['name'] ) );
			if (empty ( $teams )) {
				throw new \Exception ( 'User not found' );
			}
			$team = $teams[0];
			if (intval ( $team ['teamId'] ) == intval ( Session::$team ['teamId'] )) {
				throw new \Exception ( 'Play with yourself?' );
			}
			$response ['success'] = Challenge::getInstance ()->challengeTeam ( Session::$team ['teamId'], $team ['teamId'] );
			$response ['message'] = ($response ['success']) ? 'Challenge sent.' : 'Challenge already exists';
		
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage();
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}