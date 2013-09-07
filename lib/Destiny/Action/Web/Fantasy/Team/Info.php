<?php
namespace Destiny\Action\Web\Fantasy\Team;

use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Date;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class Info {

	/**
	 * @Route ("/fantasy/team/info")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params) {
		$ftService = TeamService::instance ();
		// Get team - Make sure this is one of the users teams
		$team = $ftService->getTeamById ( intval ( $params ['teamId'] ) );
		if (empty ( $team )) {
			throw new Exception ( 'Team not found' );
		}
		// Security
		if (Session::getCredentials()->getUserId() != $team ['userId']) {
			throw new Exception ( 'User does not have rights to this team.' );
		}
		$modifiedTime = Date::getDateTime ( $team ['modifiedDate'] );
		$createdTime = Date::getDateTime ( $team ['modifiedDate'] );
		
		$team ['teamId'] = intval ( $team ['teamId'] );
		$team ['userId'] = intval ( $team ['userId'] );
		$team ['credits'] = floor ( $team ['credits'] );
		$team ['scoreValue'] = intval ( $team ['scoreValue'] );
		$team ['transfersRemaining'] = intval ( $team ['transfersRemaining'] );
		$team ['createdDate'] = $createdTime->format ( Date::FORMAT );
		$team ['modifiedDate'] = $modifiedTime->format ( Date::FORMAT );
		$team ['champions'] = TeamService::instance ()->getTeamChamps ( $team ['teamId'] );
		
		Http::checkIfModifiedSince ( $modifiedTime->getTimestamp (), true );
		Http::header ( Http::HEADER_LAST_MODIFIED, $modifiedTime->format ( 'r' ) );
		Http::header ( Http::HEADER_CACHE_CONTROL, 'private' );
		Http::header ( Http::HEADER_PRAGMA, 'public' );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $team ) );
	}

}