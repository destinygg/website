<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Config;
use Destiny\Common\Service\LeagueApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$streamInfo = $cacheDriver->fetch ( 'streaminfo' );
		if (! empty ( $streamInfo ['stream'] )) {
			$response = LeagueApiService::instance ()->getSummoners ();
			$cacheDriver->save ( 'summoners', $response );
		}
	}

}
