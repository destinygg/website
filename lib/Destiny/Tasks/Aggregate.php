<?php

namespace Destiny\Tasks;

use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\GameService;
use Destiny\Service\Fantasy\GameAggregationService;

class Aggregate {

	public function execute(LoggerInterface $log) {
		$log->debug ( 'Aggregating games' );
		$fgService = GameService::instance ();
		$faService = GameAggregationService::instance ();
		$aggregateGames = $fgService->getUnaggregatedGames ( 1 );
		foreach ( $aggregateGames as $aggregateGame ) {
			$log->debug ( 'Aggregate #' . $aggregateGame ['gameId'] );
			$faService->aggregateGame ( $aggregateGame ['gameId'] );
		}
		$task = new \Destiny\Tasks\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Tasks\Champions ();
		$task->execute ( $log );
	}

}