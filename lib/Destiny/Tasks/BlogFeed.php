<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Psr\Log\LoggerInterface;
use Destiny\Blog\BlogApiService;

class BlogFeed {

	public function execute(LoggerInterface $log) {
		$response = BlogApiService::instance ()->getBlogPosts ()->getResponse ();
		if (! empty ( $response ))
			Application::instance ()->getCacheDriver ()->save ( 'recentblog', $response );
	}

}