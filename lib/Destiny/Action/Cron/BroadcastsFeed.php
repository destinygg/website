<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\TwitchApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class BroadcastsFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
		$app->getCacheDriver ()->save ( 'pastbroadcasts', $response );
	}

}