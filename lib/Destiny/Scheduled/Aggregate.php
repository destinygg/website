<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;

class Aggregate {

	public function execute(LoggerInterface $log) {
		$log->info ( 'Aggregating games' );
		$fgService = \Destiny\Service\Fantasy\Db\Game::getInstance ();
		$faService = \Destiny\Service\Fantasy\Db\Aggregate::getInstance ();
		$aggregateGames = $fgService->getUnaggregatedGames ( 1 );
		foreach ( $aggregateGames as $aggregateGame ) {
			$log->info ( 'Aggregate #' . $aggregateGame ['gameId'] );
			$faService->aggregateGame ( $aggregateGame ['gameId'] );
		}
		$log->info ( 'Aggregated ' . count ( $aggregateGames ) . ' game(s)' );
	}

}