<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Psr\Log\LoggerInterface;

class LeagueStatus {

	public function execute(LoggerInterface $log) {
		$log->debug ( 'Updated lol status' );
		$response = LeagueApiService::instance ()->getStatus ()->getResponse ();
		$app = Application::instance ();
		$app->getCacheDriver ()->save ( 'leaguestatus', $response );
	}

}