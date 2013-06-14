<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class YoutubeFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$cache = $app->getMemoryCache ( 'youtubeplaylist' );
		$response = CommonApiService::instance ()->getYoutubePlaylist ()->getResponse ();
		$cache->write ( $response );
	}

}