<?php
namespace Destiny\Action\Web\Fantasy\Challenge;

use Destiny\Common\HttpEntity;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Add {

	/**
	 * @Route ("/fantasy/challenge/add")
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
		try {
			if (! isset ( $params ['name'] ) || empty ( $params ['name'] )) {
				throw new Exception ( 'Name required.' );
			}
			$teamService = TeamService::instance ();
			$teams = $teamService->getTeamsByUsername ( $params ['name'] );
			if (empty ( $teams )) {
				throw new Exception ( 'User not found' );
			}
			$team = $teams [0];
			if (intval ( $team ['teamId'] ) == intval ( Session::get ( 'teamId' ) )) {
				throw new Exception ( 'Play with yourself?' );
			}
			$response ['success'] = ChallengeService::instance ()->challengeTeam ( Session::get ( 'teamId' ), $team ['teamId'] );
			$response ['message'] = ($response ['success']) ? 'Challenge sent.' : 'Challenge already exists';
		} catch ( \Exception $e ) {
			$response ['success'] = false;
			$response ['message'] = $e->getMessage ();
		}
		
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $response ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}