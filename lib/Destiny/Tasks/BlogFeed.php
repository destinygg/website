<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Blog\BlogApiService;
use Destiny\Common\TaskInterface;

/**
 * @Schedule(frequency=60,period="minute")
 */
class BlogFeed implements TaskInterface {

    public function execute() {
        $response = BlogApiService::instance ()->getBlogPosts ()->getResponse ();
        if (! empty ( $response ))
            Application::instance ()->getCacheDriver ()->save ( 'recentblog', $response );
    }

}