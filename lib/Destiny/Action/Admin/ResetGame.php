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
		$log = Application::getInstance ()->getLogger ();
		$log->notice ( sprintf ( 'Game %s reset', $params ['gameId'] ) );
		GameAggregationService::getInstance ()->resetGame ( $params ['gameId'] );
		
		$task = new \Destiny\Scheduled\Leaderboards ();
		$task->execute ( $log );
	}

}