<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Service\TwitchApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$cacheDriver = Application::instance ()->getCacheDriver ();
		$response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
		$cacheDriver->save ( 'streaminfo', $response );
	}

}