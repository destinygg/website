<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\TaskInterface;
use Destiny\LastFm\LastFMApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class LastFmFeed implements TaskInterface {

    public function execute() {
        $app = Application::instance ();
        $response = LastFMApiService::instance ()->getLastFMTracks ()->getResponse ();
        if (! empty ( $response ))
            $app->getCacheDriver ()->save ( 'recenttracks', $response );
    }

}