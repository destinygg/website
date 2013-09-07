<?php
namespace Destiny\Action\Web\Fantasy\Challenge;

use Destiny\Common\Service\Fantasy\ChallengeService;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Exception;
use Destiny\Common\Session;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class Decline {

	/**
	 * @Route ("/fantasy/challenge/decline")
	 * @Secure ({"USER"})
	 * @Transactional
	 *
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params) {
		if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
			throw new Exception ( 'teamId required.' );
		}
		if (intval ( $params ['teamId'] ) == intval ( Session::get ( 'teamId' ) )) {
			throw new Exception ( 'Play with yourself?' );
		}
		$response = array (
			'success' => true,
			'data' => array (),
			'message' => '' 
		);
		$response ['response'] = ChallengeService::instance ()->declineChallenge ( intval ( $params ['teamId'] ), intval ( Session::get ( 'teamId' ) ) );
		$response ['message'] = ($response ['response']) ? 'Declined' : 'Failed!';
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}
