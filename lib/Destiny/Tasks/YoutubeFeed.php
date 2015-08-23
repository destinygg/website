<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\Youtube\YoutubeApiService;
use TaskInterface;

class YoutubeFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $response = YoutubeApiService::instance ()->getYoutubePlaylist ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'youtubeplaylist', $response );
    }

}