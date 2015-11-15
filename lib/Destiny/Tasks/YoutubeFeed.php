<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\Youtube\YoutubeApiService;

/**
 * @Schedule(frequency=30,period="minute")
 */
class YoutubeFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $response = YoutubeApiService::instance ()->getYoutubePlaylist ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'youtubeplaylist', $response );
    }

}