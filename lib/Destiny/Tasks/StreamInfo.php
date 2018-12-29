<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Images\ImageDownloadUtil;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class StreamInfo implements TaskInterface {

    /**
     * @return mixed|void
     */
    public function execute() {
        $cache = Application::getNsCache();
        $twitchApiService = TwitchApiService::instance();
        $info = $twitchApiService->getStreamStatus(
            Config::$a['twitch']['user'],
            $cache->fetch('lasttimeonline')
        );
        if (!empty($info)) {
            $cache->save('lasttimeonline', $info['ended_at']);
            $path = ImageDownloadUtil::download($info['preview'], true);
            if (!empty($path)) {
                $info['preview'] = Config::cdni() . '/' . $path;
            }
            $cache->save('streamstatus', $info);
        }
    }

}