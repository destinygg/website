<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Destiny\Service\TwitchApiService;
use Psr\Log\LoggerInterface;
use Destiny\Application;
use Destiny\Cache\Apc;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
		$cache = $app->getMemoryCache ( 'streaminfo' );
		$cache->write ( $response );
	}

}