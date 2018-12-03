<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\LibSyn\LibSynFeedService;

/**
 * @Schedule(frequency=15,period="minute")
 */
class LibSynFeed implements TaskInterface {

    function execute() {
        $libSynService = LibSynFeedService::instance();
        $feed = $libSynService->getFeed(Config::$a['libsyn']['user']);
        if (!empty($feed)) {
            $cache = Application::getNsCache();
            $cache->save('libsynfeed', $feed);
        }
    }

}
