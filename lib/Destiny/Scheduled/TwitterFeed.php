<?php

namespace Destiny\Scheduled;

use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Twitter;
use Destiny\Application;

class TwitterFeed {

	public function execute(LoggerInterface $log) {
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'twitter' );
		$response = Twitter::getInstance ()->getTimeline ()->getResponse ();
		$cache->write ( $response );
	}

}