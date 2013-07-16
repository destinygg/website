<?php
namespace Destiny\Action\Cron;

use Destiny\Common\Config;
use Destiny\Common\Service\TwitchApiService;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;

class StreamInfo {

	public function execute(LoggerInterface $log) {
		$app = Application::instance ();
		$response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
		$app->getCacheDriver ()->save ( 'streaminfo', $response );
	}

}