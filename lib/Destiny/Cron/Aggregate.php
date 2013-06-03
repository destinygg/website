<?php
namespace Destiny\Cron;

use Destiny\Logger;

class Aggregate {
	
	public function execute(Logger $log) {
		$log->log ( 'Aggregating games' );
		$fgService = \Destiny\Service\Fantasy\Db\Game::getInstance ();
		$faService = \Destiny\Service\Fantasy\Db\Aggregate::getInstance ();
		$aggregateGames = $fgService->getUnaggregatedGames ( 1 );
		foreach ( $aggregateGames as $aggregateGame ) {
			$log->log ( 'Aggregate #' . $aggregateGame ['gameId'] );
			$faService->aggregateGame ( $aggregateGame ['gameId'] );
		}
		$log->log ( 'Aggregated ' . count ( $aggregateGames ) . ' game(s)' );
	}

}