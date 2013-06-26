<?php

namespace Destiny\Tasks;

use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\ChampionService;

class Freechamps {

	public function execute(LoggerInterface $log) {
		ChampionService::instance ()->updateFreeChampions ();
		$cron = new \Destiny\Tasks\Champions ();
		$cron->execute ( $log );
		$log->debug ( 'Rotated free champions' );
	}

}