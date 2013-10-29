<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\LeagueofLegends\LeagueApiService;
use Psr\Log\LoggerInterface;

class SummonersFeed {

	public function execute(LoggerInterface $log) {
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$streamInfo = $cacheDriver->fetch ( 'streaminfo' );
		if (! empty ( $streamInfo ['stream'] )) {
			$response = LeagueApiService::instance ()->getSummoners ();
			if (! empty ( $response ))
				$cacheDriver->save ( 'summoners', $response );
		}
	}

}
