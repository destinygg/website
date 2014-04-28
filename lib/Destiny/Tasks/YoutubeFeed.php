<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Psr\Log\LoggerInterface;
use Destiny\Youtube\YoutubeApiService;

class YoutubeFeed {

    public function execute(LoggerInterface $log) {
        $app = Application::instance ();
        $response = YoutubeApiService::instance ()->getYoutubePlaylist ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'youtubeplaylist', $response );
    }

}