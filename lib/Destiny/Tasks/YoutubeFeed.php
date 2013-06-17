<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class YoutubeFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = CommonApiService::instance ()->getYoutubePlaylist ()->getResponse ();
		$app->getCacheDriver ()->save ( 'youtubeplaylist', $response );
	}

}