<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class BlogFeed {

	public function execute(LoggerInterface $log) {
		$response = CommonApiService::getInstance ()->getBlogPosts ()->getResponse ();
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'recentblog' );
		$cache->write ( $response );
	}

}