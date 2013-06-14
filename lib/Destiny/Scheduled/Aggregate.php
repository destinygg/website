<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;

use Destiny\Service\Fantasy\GameService;
use Destiny\Service\Fantasy\GameAggregationService;

class Aggregate {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Aggregating games' );
		$fgService = GameService::instance ();
		$faService = GameAggregationService::instance ();
		$aggregateGames = $fgService->getUnaggregatedGames ( 1 );
		foreach ( $aggregateGames as $aggregateGame ) {
			$log->info ( 'Aggregate #' . $aggregateGame ['gameId'] );
			$faService->aggregateGame ( $aggregateGame ['gameId'] );
		}
		$task = new \Destiny\Scheduled\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Scheduled\Champions ();
		$task->execute ( $log );
	}

}