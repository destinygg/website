<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Blog\BlogApiService;
use Destiny\Common\Cron\TaskInterface;

/**
 * @Schedule(frequency=60,period="minute")
 */
class BlogFeed implements TaskInterface {

    public function execute() {
        $response = BlogApiService::instance()->getBlogPosts();
        if (!empty ($response)) {
            $cache = Application::getNsCache();
            $cache->save('recentblog', $response);
        }
    }

}