<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Destiny\Service\Twitch;
use Psr\Log\LoggerInterface;
use Destiny\Application;
use Destiny\Cache\Apc;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$response = Twitch::getInstance ()->getStreamInfo ()->getResponse ();
		$cache = $app->getMemoryCache ( 'streaminfo' );
		$cache->write ( $response );
	}

}