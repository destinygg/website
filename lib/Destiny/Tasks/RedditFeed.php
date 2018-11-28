<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Reddit\RedditFeedService;

/**
 * @Schedule(frequency=10,period="minute")
 */
class RedditFeed implements TaskInterface {

    /**
     * @return mixed|void
     */
    public function execute() {
        $redditService = RedditFeedService::instance();
        $posts = $redditService->getHotThreads();
        if (!empty ($posts)) {
            Application::instance()->getCache()->save('recentposts', $posts);
        }
    }

}