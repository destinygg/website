<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Blog\BlogApiService;

class BlogFeed {

    public function execute() {
        $response = BlogApiService::instance ()->getBlogPosts ()->getResponse ();
        if (! empty ( $response ))
            Application::instance ()->getCacheDriver ()->save ( 'recentblog', $response );
    }

}