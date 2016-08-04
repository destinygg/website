<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\ImageDownload;
use Destiny\Youtube\YoutubeApiService;

/**
 * @Schedule(frequency=30,period="minute")
 */
class YoutubeFeed implements TaskInterface {

    public function execute() {
        $json = YoutubeApiService::instance()->getYoutubePlaylist()->getResponse();
        if (!empty ($json)) {
            foreach ($json ['items'] as $i => $item) {
                $json ['items'][$i]['image'] = "http://i.ytimg.com/vi/" . $item['snippet']['resourceId']['videoId'] . "/default.jpg";
                $path = ImageDownload::download($json ['items'][$i]['image'], Config::$a['images']['path']);
                if (!empty($path))
                    $json ['items'][$i]['image'] = Config::cdn() . '/img/' . $path;
            }
            $cache = Application::instance()->getCacheDriver();
            $cache->save('youtubeplaylist', $json);
        }
    }

}