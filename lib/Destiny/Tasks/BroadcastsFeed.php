<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Twitch\TwitchApiService;
use TaskInterface;

class BroadcastsFeed  implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'pastbroadcasts', $response );
    }

}