<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = LeagueApiService::instance ()->getSummoners ();
		$app->getCacheDriver ()->save ( 'summoners', $response );
	}

}
