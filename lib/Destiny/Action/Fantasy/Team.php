<?php

namespace Destiny\Action\Fantasy;

use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\Config;

class Team {

	public function execute(array $params) {
		$ftService = \Destiny\Service\Fantasy\Db\Team::getInstance ();
		// Get team - Make sure this is one of the users teams
		$team = $ftService->getTeamById ( ( int ) $params ['teamId'] );
		if (empty ( $team )) {
			throw new \Exception ( 'Team not found' );
		}
		if (! Session::authorized ()) {
			Http::status ( 401 );
			exit ();
		}
		// Security
		if (Session::get ( 'userId' ) != $team ['userId']) {
			throw new \Exception ( 'Get team failed:  User does not have rights to this team. {"userId":' . $team ['userId'] . ',"teamId":' . $team ['teamId'] . '}' );
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
		$team ['champions'] = Team::getInstance ()->getTeamChamps ( $team ['teamId'] );
		
		Http::checkIfModifiedSince ( $modifiedTime, true );
		Http::header ( Http::HEADER_LAST_MODIFIED, gmdate ( 'r', $modifiedTime ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $team ) );
	}

}