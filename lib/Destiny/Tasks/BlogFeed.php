<?php

namespace Destiny\Tasks;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\CommonApiService;

class BlogFeed {

	public function execute(LoggerInterface $log) {
		$response = CommonApiService::instance ()->getBlogPosts ()->getResponse ();
		$app = Application::instance ();
		$app->getCacheDriver ()->save ( 'recentblog', $response );
	}

}