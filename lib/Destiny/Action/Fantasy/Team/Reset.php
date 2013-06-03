<?php
namespace Destiny\Action\Fantasy\Team;

use Destiny\Service\Fantasy\Db\Team;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Utils\Http;
use Destiny\Mimetype;
use Destiny\Session;

class Reset {

	public function execute(array $params) {
		$response = array (
			'success' => false,
			'data' => array (),
			'message' => ''
		);
		try {
			
			if ($_SERVER['REQUEST_METHOD'] != 'POST') {
				throw new \Exception ( 'POST required' );
			}
			if (! Session::getAuthorized ()) {
				throw new \Exception ( 'User required' );
			}
			
			// Get team - Make sure this is one of the users teams
			$team = Team::getInstance ()->getTeamByUserId ( Session::$userId );
			if(empty($team)){
				throw new \Exception ( 'User team not found' );
			}
			// Security
			if (Session::$userId != $team ['userId']) {
				throw new \Exception ( 'Reset team failed: User does not have rights to this team. {"userId":'.$team ['userId'].',"teamId":'.$team ['teamId'].'}' );
			}
			// Reset team vars
			Team::getInstance ()->resetTeam ( $team );
			$response ['data'] = $team;
			$response ['success'] = true;
			
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage ();
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}
}