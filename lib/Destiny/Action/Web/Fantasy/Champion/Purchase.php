<?php
namespace Destiny\Action\Web\Fantasy\Champion;

use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Service\Fantasy\ChampionService;
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
class Purchase {

	/**
	 * @Route ("/fantasy/champion/purchase")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params) {
		$response = array (
			'success' => true,
			'data' => array (),
			'message' => '' 
		);
		if (! isset ( $params ['championId'] ) || empty ( $params ['championId'] )) {
			throw new Exception ( 'championId parameter required' );
		}
		if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
			throw new Exception ( 'teamId parameter required' );
		}
		$team = $this->updateTeam ( $params );
		$response ['data'] = $team;
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

	private function updateTeam(array $params) {
		$teamService = TeamService::instance ();
		$champService = ChampionService::instance ();
		// Get team - Make sure this is one of the users teams
		$team = $teamService->getTeamById ( ( int ) $params ['teamId'] );
		if (empty ( $team )) {
			throw new Exception ( 'Team not found' );
		}
		// Security
		if (Session::getCredentials()->getUserId() != $team ['userId']) {
			throw new Exception ( 'Update team failed: User does not have rights to this team. {"userId":' . $team ['userId'] . ',"teamId":' . $team ['teamId'] . '}' );
		}
		$champ = $champService->getChampionById ( $params ['championId'] );
		$team ['credits'] = floatval ( $team ['credits'] );
		if ($team ['credits'] - floatval ( $champ ['championValue'] ) < 0) {
			throw new Exception ( 'Not enough credits' );
		}
		$team ['credits'] = $team ['credits'] - floatval ( $champ ['championValue'] );
		$champService->unlockChampion ( Session::getCredentials()->getUserId(), $champ ['championId'] );
		$teamService->updateTeamResources ( $team );
		return $team;
	}

}