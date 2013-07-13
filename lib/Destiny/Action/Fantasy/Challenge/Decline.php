<?php
namespace Destiny\Action\Fantasy\Challenge;

use Destiny\Service\Fantasy\ChallengeService;
use Destiny\Utils\Http;
use Destiny\MimeType;
use Destiny\AppException;
use Destiny\Session;
use Destiny\Annotation\Action;
use Destiny\Annotation\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;

/**
 * @Action
 */
class Decline {

	/**
	 * @Route ("/fantasy/challenge/decline")
	 * @Secure ({"USER"})
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function execute(array $params) {
		if (! isset ( $params ['teamId'] ) || empty ( $params ['teamId'] )) {
			throw new AppException ( 'teamId required.' );
		}
		if (intval ( $params ['teamId'] ) == intval ( Session::get ( 'teamId' ) )) {
			throw new AppException ( 'Play with yourself?' );
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
