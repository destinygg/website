<?php
namespace Destiny\Action\Web\Fantasy\Challenge;

use Destiny\Common\HttpEntity;
use Destiny\Common\Service\Fantasy\ChallengeService;
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
class Delete {

	/**
	 * @Route ("/fantasy/challenge/delete")
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
		$response ['response'] = ChallengeService::instance ()->deleteChallenge ( intval ( Session::get ( 'teamId' ) ), intval ( $params ['teamId'] ) );
		$response ['message'] = ($response ['response']) ? 'Deleted' : 'Failed!';
		
		$response = new HttpEntity ( Http::STATUS_OK, json_encode ( $response ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}

}
