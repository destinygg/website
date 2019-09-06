<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Images\ImageDownloadUtil;
use Destiny\LastFm\LastFMApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class LastFmFeed implements TaskInterface {

    public function execute() {
        $json = LastFMApiService::instance()->getLastPlayedTracks();
        if (!empty ($json)) {
            foreach ($json ['recenttracks'] ['track'] as $i => $track) {
                $path = ImageDownloadUtil::download($track['image'][1]['#text']);
                if (!empty($path)) {
                    $json ['recenttracks'] ['track'] [$i] ['image'][1]['#text'] = Config::cdni() . '/' . $path;
                }
            }
            $cache = Application::getNsCache();
            $cache->save('recenttracks', $json);
        }
        $json = LastFMApiService::instance()->getTopTracks();
        if (!empty ($json)) {
            foreach ($json ['toptracks'] ['track'] as $i => $track) {
                $path = ImageDownloadUtil::download($track['image'][1]['#text']);
                if (!empty($path)) {
                    $json ['toptracks'] ['track'] [$i] ['image'][1]['#text'] = Config::cdni() . '/' . $path;
                }
            }
            $cache = Application::getNsCache();
            $cache->save('toptracks', $json);
        }
    }

}