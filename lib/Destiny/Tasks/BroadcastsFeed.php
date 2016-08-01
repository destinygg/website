<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=30,period="minute")
 */
class BroadcastsFeed  implements TaskInterface {

    public function execute() {
        $cache = Application::instance ()->getCacheDriver ();
        $response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
        if (! empty ( $response ))
            $cache->save ( 'pastbroadcasts', $response );
    }

}