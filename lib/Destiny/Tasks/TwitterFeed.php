<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Twitter\TwitterApiService;

/**
 * @Schedule(frequency=30,period="minute")
 */
class TwitterFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $cacheDriver = $app->getCache ();
        $cacheDriver->save('twitter', TwitterApiService::instance()->getTweets());
    }

}