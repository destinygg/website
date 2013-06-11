<?php

namespace Destiny\Scheduled;

use Destiny\Application;
use Destiny\Config;
use Psr\Log\LoggerInterface;
use Destiny\Service\Blog;

class BlogFeed {

	public function execute(LoggerInterface $log) {
		$response = Blog::getInstance ()->getRecent ()->getResponse ();
		$app = Application::getInstance ();
		$cache = $app->getMemoryCache ( 'recentblog' );
		$cache->write ( $response );
	}

}