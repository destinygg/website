<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\ChampionService;

class Freechamps {

	public function execute(LoggerInterface $log) {
		ChampionService::getInstance ()->updateFreeChampions ();
		$log->info ( 'Rotated free champions' );
	}

}