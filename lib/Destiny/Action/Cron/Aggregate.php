<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Service\Fantasy\GameAggregationService;
use Psr\Log\LoggerInterface;

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
		$task = new \Destiny\Cron\Action\Leaderboards ();
		$task->execute ( $log );
		$task = new \Destiny\Cron\Action\Champions ();
		$task->execute ( $log );
	}

}