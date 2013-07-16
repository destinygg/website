<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\Fantasy\ChampionService;
use Psr\Log\LoggerInterface;

class Freechamps {

	public function execute(LoggerInterface $log) {
		ChampionService::instance ()->updateFreeChampions ();
		$cron = new \Destiny\Action\Cron\Champions ();
		$cron->execute ( $log );
		$log->debug ( 'Rotated free champions' );
	}

}