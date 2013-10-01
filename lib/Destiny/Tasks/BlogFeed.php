<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Psr\Log\LoggerInterface;
use Destiny\Blog\BlogApiService;

class BlogFeed {

	public function execute(LoggerInterface $log) {
		$response = BlogApiService::instance ()->getBlogPosts ()->getResponse ();
		$app = Application::instance ();
		$app->getCacheDriver ()->save ( 'recentblog', $response );
	}

}