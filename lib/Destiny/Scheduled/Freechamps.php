<?php

namespace Destiny\Scheduled;

use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\Db\Champion;

class Freechamps {

	public function execute(LoggerInterface $log) {
		Champion::getInstance ()->updateFreeChampions ();
		$log->info ( 'Rotated free champions' );
	}

}