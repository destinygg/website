<?php

namespace Destiny\Action\Fantasy\Champion;

use Destiny\Service\Fantasy\TeamService;
use Destiny\Service\Fantasy\ChampionService;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\AppException;

class Purchase {

	public function execute(array $params) {
		$response = array (
				'success' => true,
				'data' => array (),
				'message' => '' 
		);
		if (! isset ( $params ['championId'] ) || empty ( $params ['championId'] )) {
			throw new AppException ( 'championId parameter required' );
		}
		if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
			throw new AppException ( 'teamId parameter required' );
		}
		$team = $this->updateTeam ( $params );
		$response ['data'] = $team;
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

	private function updateTeam(array $params) {
		$teamService = TeamService::getInstance ();
		$champService = ChampionService::getInstance ();
		// Get team - Make sure this is one of the users teams
		$team = $teamService->getTeamById ( ( int ) $params ['teamId'] );
		if (empty ( $team )) {
			throw new AppException ( 'Team not found' );
		}
		// Security
		if (Session::get ( 'userId' ) != $team ['userId']) {
			throw new AppException ( 'Update team failed: User does not have rights to this team. {"userId":' . $team ['userId'] . ',"teamId":' . $team ['teamId'] . '}' );
		}
		$champ = $champService->getChampionById ( $params ['championId'] );
		$team ['credits'] = floatval ( $team ['credits'] );
		if ($team ['credits'] - floatval ( $champ ['championValue'] ) < 0) {
			throw new AppException ( 'Not enough credits' );
		}
		$team ['credits'] = $team ['credits'] - floatval ( $champ ['championValue'] );
		$champService->unlockChampion ( Session::get ( 'userId' ), $champ ['championId'] );
		$teamService->updateTeamResources ( $team );
		return $team;
	}

}