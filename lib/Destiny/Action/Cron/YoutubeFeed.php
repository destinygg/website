<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Common\Service\CommonApiService;
use Psr\Log\LoggerInterface;

class YoutubeFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = CommonApiService::instance ()->getYoutubePlaylist ()->getResponse ();
		$app->getCacheDriver ()->save ( 'youtubeplaylist', $response );
	}

}