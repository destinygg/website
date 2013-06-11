<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Youtube;

class YoutubeFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'youtubeplaylist' );
		$response = Youtube::getInstance ()->getPlaylist ()->getResponse ();
		$cache->write ( $response );
	}

}