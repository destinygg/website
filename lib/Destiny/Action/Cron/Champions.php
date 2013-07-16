<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Service\Fantasy\ChampionService;
use Psr\Log\LoggerInterface;

class Champions {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$champions = ChampionService::instance ()->getChampions ();
		$app->getCacheDriver ()->save ( 'champions', $champions );
	}

}