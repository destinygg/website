<?php
namespace Destiny\Tasks;

use Destiny\Common\Application;
use Destiny\LastFm\LastFMApiService;
use TaskInterface;

class LastFmFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $response = LastFMApiService::instance ()->getLastFMTracks ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'recenttracks', $response );
    }

}