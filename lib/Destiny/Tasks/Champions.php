<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Psr\Log\LoggerInterface;
use Destiny\Service\Fantasy\ChampionService;

class Champions {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$champions = ChampionService::instance ()->getChampions ();
		$app->getCacheDriver ()->save ( 'champions', $champions );
	}

}