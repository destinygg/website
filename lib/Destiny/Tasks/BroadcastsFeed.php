<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Twitch\TwitchApiService;
use Psr\Log\LoggerInterface;

class BroadcastsFeed {

    public function execute(LoggerInterface $log) {
        $app = Application::instance ();
        $response = TwitchApiService::instance ()->getPastBroadcasts ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'pastbroadcasts', $response );
    }

}