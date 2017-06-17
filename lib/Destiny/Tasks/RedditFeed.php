<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Reddit\RedditFeedService;

/**
 * @Schedule(frequency=10,period="minute")
 */
class RedditFeed implements TaskInterface {

    public function execute() {
        $redditService = RedditFeedService::instance();
        $posts = $redditService->getHotThreads();
        if (! empty ( $posts ))
            Application::instance ()->getCache ()->save( 'recentposts', $posts );
    }

}