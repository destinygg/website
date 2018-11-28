<?php
namespace Destiny\Tasks;

use Destiny\Chat\ChatRedisService;
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
        $cache = Application::instance()->getCache();
        $twitchApiService = TwitchApiService::instance();
        $redisService = ChatRedisService::instance();

        // STREAM STATUS
        $streaminfo = $twitchApiService->getStreamInfo(Config::$a ['twitch']['user']);
        if (!empty($streaminfo)) {
            $path = ImageDownloadUtil::download($streaminfo['preview'], true);
            if (!empty($path))
                $streaminfo['preview'] = Config::cdni() . '/' . $path;
            $path = ImageDownloadUtil::download($streaminfo['animated_preview'], true);
            if (!empty($path))
                $streaminfo['animated_preview'] = Config::cdni() . '/' . $path;
        }

        // STREAM HOSTING
        $lasthost = $cache->contains('streamhostinfo') ? $cache->fetch('streamhostinfo') : [];
        $currhost = $twitchApiService->getChannelHostWithInfo(Config::$a['twitch']['id']);
        if(TwitchApiService::checkForHostingChange($lasthost, $currhost) == TwitchApiService::$HOST_NOW_HOSTING){
            $redisService->sendBroadcast(sprintf(
                '%s is now hosting %s at %s',
                Config::$a['meta']['shortName'],
                $currhost['display_name'],
                $currhost['url'])
            );
        }

        // STEAM GO-LIVE ANNOUNCEMENT
        $waslive = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : null;
        if($waslive !== null && $streaminfo !== null && (isset($waslive['live']) && $waslive['live'] == false && $streaminfo['live'] == true)){
            $redisService->sendBroadcast(sprintf(
                '%s is now live!',
                Config::$a['meta']['shortName'])
            );
        }

        $streaminfo['host'] = $currhost;
        $cache->save('streamhostinfo', $currhost);
        $cache->save('streamstatus', $streaminfo);
    }

}