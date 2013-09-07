<?php
namespace Destiny\Action\Web\Fantasy\Team;

use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Reset {

	/**
	 * @Route ("/fantasy/team/reset")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 * @throws Exception
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
			throw new Exception ( 'User team not found' );
		}
		// Security
		if (Session::getCredentials()->getUserId() != $team ['userId']) {
			throw new Exception ( 'Reset team failed user does not have rights to this team.' );
		}
		// Reset team vars
		TeamService::instance ()->resetTeam ( $team );
		$response ['data'] = $team;
		$response ['success'] = true;
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}