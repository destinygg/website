<?php

namespace Destiny\Action\Fantasy;

use Destiny\Service\Fantasy\TeamService;
use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;
use Destiny\AppException;

class Challenge {

	public function execute(array $params) {
		$response = array (
				'success' => true,
				'data' => array (),
				'message' => '' 
		);
		try {
			if (! isset ( $params ['name'] ) || empty ( $params ['name'] )) {
				throw new AppException ( 'Name required.' );
			}
			$teamService = TeamService::getInstance ();
			$teams = $teamService->getTeamsByUsername ( mysql_real_escape_string ( $params ['name'] ) );
			if (empty ( $teams )) {
				throw new AppException ( 'User not found' );
			}
			$team = $teams [0];
			if (intval ( $team ['teamId'] ) == intval ( Session::get ( 'teamId' ) )) {
				throw new AppException ( 'Play with yourself?' );
			}
			$response ['success'] = ChallengeService::getInstance ()->challengeTeam ( Session::get ( 'teamId' ), $team ['teamId'] );
			$response ['message'] = ($response ['success']) ? 'Challenge sent.' : 'Challenge already exists';
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage ();
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}