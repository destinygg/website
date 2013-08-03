<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Psr\Log\LoggerInterface;

class LeagueStatus {

	public function execute(LoggerInterface $log) {
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$streamInfo = $cacheDriver->fetch ( 'streaminfo' );
		if (! empty ( $streamInfo ['stream'] )) {
			$log->debug ( 'Updated lol status' );
			$response = LeagueApiService::instance ()->getStatus ()->getResponse ();
			$cacheDriver->save ( 'leaguestatus', $response );
		}
	}

}