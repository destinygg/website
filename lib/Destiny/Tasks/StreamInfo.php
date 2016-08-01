<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class StreamInfo implements TaskInterface {

    public function execute() {
        $cache = Application::instance()->getCacheDriver();
        $streaminfo = TwitchApiService::instance()->getStreamInfo();
        if (!empty ($streaminfo))
            $cache->save('streamstatus', $streaminfo);
    }

}