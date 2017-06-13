<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\ImageDownload;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=30,period="minute")
 */
class BroadcastsFeed  implements TaskInterface {

    public function execute() {
        $broadcasts = TwitchApiService::instance ()->getPastBroadcasts ();
        if (! empty ( $broadcasts )){
            foreach ($broadcasts['videos'] as $i=>$video){
                $path = ImageDownload::download($video['preview']);
                if(!empty($path))
                    $broadcasts['videos'][$i]['preview'] = Config::cdni() . '/' . $path;
            }
            $cache = Application::instance ()->getCacheDriver ();
            $cache->save ( 'pastbroadcasts', $broadcasts );
        }
    }

}