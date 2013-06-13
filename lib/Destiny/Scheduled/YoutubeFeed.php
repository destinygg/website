<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class YoutubeFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'youtubeplaylist' );
		$response = CommonApiService::getInstance ()->getYoutubePlaylist ()->getResponse ();
		$cache->write ( $response );
	}

}