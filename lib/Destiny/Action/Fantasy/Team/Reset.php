<?php

namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;
use Destiny\AppException;

class Reset {

	public function execute(array $params) {
		$response = array (
				'success' => false,
				'data' => array (),
				'message' => '' 
		);
		// Get team - Make sure this is one of the users teams
		$team = Team::getInstance ()->getTeamByUserId ( Session::get ( 'userId' ) );
		if (empty ( $team )) {
			throw new AppException ( 'User team not found' );
		}
		// Security
		if (Session::get ( 'userId' ) != $team ['userId']) {
			throw new AppException ( 'Reset team failed user does not have rights to this team.' );
		}
		// Reset team vars
		Team::getInstance ()->resetTeam ( $team );
		$response ['data'] = $team;
		$response ['success'] = true;
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}