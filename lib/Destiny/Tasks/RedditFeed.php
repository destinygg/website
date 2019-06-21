<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Reddit\RedditService;

/**
 * @Schedule(frequency=10,period="minute")
 */
class RedditFeed implements TaskInterface {

    public function execute() {
        $redditService = RedditService::instance();
        $posts = $redditService->getHotThreads();
        if (!empty ($posts)) {
            $cache = Application::getNsCache();
            $cache->save('recentposts', $posts);
        }
    }

}