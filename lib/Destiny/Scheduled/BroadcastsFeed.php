<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Twitch;
use Destiny\Application;

class BroadcastsFeed {

	public function execute(LoggerInterface $log) {
		$response = Twitch::getInstance ()->getPastBroadcasts ()->getResponse ();
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'pastbroadcasts' );
		$cache->write ( $response );
	}

}