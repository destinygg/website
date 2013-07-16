<?php
namespace Destiny\Action\Admin;

use Destiny\Common\AppException;
use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\GameAggregationService;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;

/**
 * @Action
 */
class DeleteGame {

	/**
	 * @Route ("/admin/deletegame")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 * @throws AppException
	 */
	public function execute(array $params) {
		if (! isset ( $params ['gameId'] ) || empty ( $params ['gameId'] )) {
			throw new AppException ( 'gameId required.' );
		}
		$log = Application::instance ()->getLogger ();
		$log->notice ( sprintf ( 'Game %s reset', $params ['gameId'] ) );
		GameAggregationService::instance ()->resetGame ( $params ['gameId'] );
		GameAggregationService::instance ()->removeGame ( $params ['gameId'] );
		GameAggregationService::instance ()->calculateTeamScore ();
		GameAggregationService::instance ()->calculateTeamRanks ();
		$task = new \Destiny\Action\Cron\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Action\Cron\Champions ();
		$task->execute ( $log );
	}

}