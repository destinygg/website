<?php
namespace Destiny\Action\Fantasy\Champion;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;

class Purchase {

	public function execute(array $params) {
		$response = array (
				'success' => true,
				'data' => array (),
				'message' => '' 
		);
		try {
			if (! Session::getAuthorized ()) {
				throw new \Exception ( 'User required' );
			}
			if (! isset ( $params ['championId'] ) || empty ( $params ['championId'] )) {
				throw new \Exception ( 'championId parameter required' );
			}
			if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
				throw new \Exception ( 'teamId parameter required' );
			}
			$team = $this->updateTeam ( $params );
			$response ['data'] = $team;
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = 'Hamsters....'; // $e->getMessage()
		}
		
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

	private function updateTeam(array $params) {
		$teamService = Team::getInstance ();
		$champService = Champion::getInstance ();
		// Get team - Make sure this is one of the users teams
		$team = $teamService->getTeamById ( ( int ) $params ['teamId'] );
		if (empty ( $team )) {
			throw new \Exception ( 'Team not found' );
		}
		// Security
		if (Session::$userId != $team ['userId']) {
			throw new \Exception ( 'Update team failed: User does not have rights to this team. {"userId":'.$team ['userId'].',"teamId":'.$team ['teamId'].'}' );
		}
		$champ = $champService->getChampionById ( $params ['championId'] );
		$team ['credits'] = floatval ( $team ['credits'] );
		if ($team ['credits'] - floatval ( $champ ['championValue'] ) < 0) {
			throw new \Exception ( 'Not enough credits' );
		}
		$team ['credits'] = $team ['credits'] - floatval ( $champ ['championValue'] );
		$champService->unlockChampion ( Session::$userId, $champ ['championId'] );
		$teamService->updateTeamResources ( $team );
		return $team;
	}

}