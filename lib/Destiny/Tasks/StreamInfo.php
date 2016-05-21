<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\Date;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class StreamInfo implements TaskInterface {

    public function execute() {
        $cacheDriver = Application::instance()->getCacheDriver();
        $response = TwitchApiService::instance()->getStreamInfo()->getResponse();
        if (!empty ($response)) {
            if ($response['live'])
                $cacheDriver->save('lastbroadcast', Date::getDateTime()->format(Date::FORMAT));
            $cacheDriver->save('streaminfo', $response);
        }
    }

}