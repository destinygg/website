<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;

use Destiny\Service\Fantasy\GameService;
use Destiny\Service\Fantasy\GameAggregationService;

class Aggregate {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Aggregating games' );
		$fgService = GameService::getInstance ();
		$faService = GameAggregationService::getInstance ();
		$aggregateGames = $fgService->getUnaggregatedGames ( 1 );
		foreach ( $aggregateGames as $aggregateGame ) {
			$log->info ( 'Aggregate #' . $aggregateGame ['gameId'] );
			$faService->aggregateGame ( $aggregateGame ['gameId'] );
		}
		$task = new \Destiny\Scheduled\Leaderboards ();
		$task->execute ( $log );
	}

}