<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\ImageDownload;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class StreamInfo implements TaskInterface {

    public function execute() {
        $streaminfo = TwitchApiService::instance()->getStreamInfo();
        if (!empty ($streaminfo)){
            $path = ImageDownload::download($streaminfo['preview'], Config::$a['images']['path']);
            if(!empty($path))
                $streaminfo['preview'] =  Config::cdn() . '/img/' . $path;
            $path = ImageDownload::download($streaminfo['animated_preview'], Config::$a['images']['path']);
            if(!empty($path))
                $streaminfo['animated_preview'] = Config::cdn() . '/img/' . $path;
            $cache = Application::instance()->getCacheDriver();
            $cache->save('streamstatus', $streaminfo);
        }
    }

}