<?php

namespace Destiny\Tasks;

use Destiny\Config;
use Destiny\Service\TwitchApiService;
use Psr\Log\LoggerInterface;
use Destiny\Application;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
		$app->getCacheDriver ()->save ( 'streaminfo', $response );
	}

}