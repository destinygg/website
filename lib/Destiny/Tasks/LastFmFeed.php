<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\ImageDownload;
use Destiny\LastFm\LastFMApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class LastFmFeed implements TaskInterface {

    public function execute() {
        $json = LastFMApiService::instance ()->getLastFMTracks ()->getResponse ();
        if (! empty ( $json )){
            foreach ( $json ['recenttracks'] ['track'] as $i => $track ) {
                $path = ImageDownload::download($track['image'][1]['#text'], Config::$a['images']['path']);
                if (!empty($path))
                    $json ['recenttracks'] ['track'] [$i] ['image'][1]['#text'] = Config::cdn() . '/img/' . $path;
            }
            $cache = Application::instance()->getCacheDriver();
            $cache->save ( 'recenttracks', $json );
        }
    }

}