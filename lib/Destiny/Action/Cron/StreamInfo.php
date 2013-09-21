<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Application;
use Destiny\Twitch\Service\TwitchApiService;
use Psr\Log\LoggerInterface;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
		$cacheDriver->save ( 'streaminfo', $response );
	}

}