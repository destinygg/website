<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Twitch\TwitchApiService;

class StreamInfo {

    public function execute() {
        $cacheDriver = Application::instance ()->getCacheDriver ();
        $response = TwitchApiService::instance ()->getStreamInfo ()->getResponse ();
        if (! empty ( $response ))
            $cacheDriver->save ( 'streaminfo', $response );
    }

}