<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Service\CommonApiService;
use Psr\Log\LoggerInterface;

class LastFmFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = CommonApiService::instance ()->getLastFMTracks ()->getResponse ();
		if (! empty ( $response )) {
			$app->getCacheDriver ()->save ( 'recenttracks', $response );
		}
	}

}