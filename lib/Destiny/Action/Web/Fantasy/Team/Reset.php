<?php
namespace Destiny\Action\Web\Fantasy\Team;

use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\AppException;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Reset {

	/**
	 * @Route ("/fantasy/team/reset")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function execute(array $params) {
		$response = array (
			'success' => false,
			'data' => array (),
			'message' => '' 
		);
		// Get team - Make sure this is one of the users teams
		$team = TeamService::instance ()->getTeamByUserId ( Session::getCredentials()->getUserId() );
		if (empty ( $team )) {
			throw new AppException ( 'User team not found' );
		}
		// Security
		if (Session::getCredentials()->getUserId() != $team ['userId']) {
			throw new AppException ( 'Reset team failed user does not have rights to this team.' );
		}
		// Reset team vars
		TeamService::instance ()->resetTeam ( $team );
		$response ['data'] = $team;
		$response ['success'] = true;
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}