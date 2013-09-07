<?php
namespace Destiny\Action\Admin;

use Destiny\Common\Exception;
use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\GameAggregationService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;

/**
 * @Action
 */
class ResetGame {

	/**
	 * @Route ("/admin/resetgame")
	 * @Secure ({"ADMIN"})
	 * @Transactional
	 *
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params) {
		if (! isset ( $params ['gameId'] ) || empty ( $params ['gameId'] )) {
			throw new Exception ( 'gameId required.' );
		}
		$log = Application::instance ()->getLogger ();
		$log->notice ( sprintf ( 'Game %s reset', $params ['gameId'] ) );
		GameAggregationService::instance ()->resetGame ( $params ['gameId'] );
		GameAggregationService::instance ()->calculateTeamScore ();
		GameAggregationService::instance ()->calculateTeamRanks ();
		$task = new \Destiny\Action\Cron\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Action\Cron\Champions ();
		$task->execute ( $log );
	}

}