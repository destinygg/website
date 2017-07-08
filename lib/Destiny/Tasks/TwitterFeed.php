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
        $cacheDriver = Application::instance()->getCache ();
        $cacheDriver->save('twitter', TwitterApiService::instance()->getTweets());
    }

}