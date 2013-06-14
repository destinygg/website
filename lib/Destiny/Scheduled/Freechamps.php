<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\ChampionService;

class Freechamps {

	public function execute(LoggerInterface $log) {
		ChampionService::instance ()->updateFreeChampions ();
		$cron = new \Destiny\Scheduled\Champions ();
		$cron->execute ( $log );
		$log->info ( 'Rotated free champions' );
	}

}