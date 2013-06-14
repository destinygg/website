<?php

namespace Destiny\Action\Admin;

use Destiny\AppException;
use Destiny\Application;
use Destiny\Service\Fantasy\GameAggregationService;

class ResetGame {

	public function execute(array $params) {
		if (! isset ( $params ['gameId'] ) || empty ( $params ['gameId'] )) {
			throw new AppException ( 'gameId required.' );
		}
		$log = Application::instance ()->getLogger ();
		$log->notice ( sprintf ( 'Game %s reset', $params ['gameId'] ) );
		GameAggregationService::instance ()->resetGame ( $params ['gameId'] );
		GameAggregationService::instance ()->calculateTeamScore ();
		GameAggregationService::instance ()->calculateTeamRanks ();
		$task = new \Destiny\Scheduled\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Scheduled\Champions ();
		$task->execute ( $log );
	}

}