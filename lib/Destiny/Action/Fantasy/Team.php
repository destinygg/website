<?php

namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;
use Destiny\AppException;
use Destiny\Service\Fantasy\TeamService;

class Team {

	public function execute(array $params) {
		$ftService = TeamService::instance ();
		// Get team - Make sure this is one of the users teams
		$team = $ftService->getTeamById ( intval ( $params ['teamId'] ) );
		if (empty ( $team )) {
			throw new AppException ( 'Team not found' );
		}
		// Security
		if (Session::get ( 'userId' ) != $team ['userId']) {
			throw new AppException ( 'User does not have rights to this team.' );
		}
		$modifiedTime = strtotime ( $team ['modifiedDate'] );
		$createdTime = strtotime ( $team ['modifiedDate'] );
		$team ['teamId'] = intval ( $team ['teamId'] );
		$team ['userId'] = intval ( $team ['userId'] );
		$team ['credits'] = floor ( $team ['credits'] );
		$team ['scoreValue'] = intval ( $team ['scoreValue'] );
		$team ['transfersRemaining'] = intval ( $team ['transfersRemaining'] );
		$team ['createdDate'] = Date::getDateTime ( $createdTime, Date::FORMAT );
		$team ['modifiedDate'] = Date::getDateTime ( $modifiedTime, Date::FORMAT );
		$team ['champions'] = Team::instance ()->getTeamChamps ( $team ['teamId'] );
		
		Http::checkIfModifiedSince ( $modifiedTime, true );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $modifiedTime ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $team ) );
	}

}