<?php
namespace Destiny\Cron;

use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Logger;

class Freechamps {

	public function execute(Logger $log) {
		Champion::getInstance ()->updateFreeChampions ();
		$log->log ( 'Free champs checked' );
	}

}