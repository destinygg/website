<?php
namespace Destiny\Tasks;

use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\TaskInterface;
use Destiny\Common\Utils\ImageDownload;
use Destiny\Twitch\TwitchApiService;

/**
 * @Schedule(frequency=1,period="minute")
 */
class StreamInfo implements TaskInterface {

    /**
     * @return mixed|void
     * @throws \Destiny\Common\Exception
     */
    public function execute() {
        $cache = Application::instance()->getCache();
        $twitchApiService = TwitchApiService::instance();
        $chatIntegration = ChatIntegrationService::instance();

        // STREAM STATUS
        $streaminfo = $twitchApiService->getStreamInfo(Config::$a ['twitch']['user']);
        if (!empty($streaminfo)) {
            $path = ImageDownload::download($streaminfo['preview'], true);
            if (!empty($path))
                $streaminfo['preview'] = Config::cdni() . '/' . $path;
            $path = ImageDownload::download($streaminfo['animated_preview'], true);
            if (!empty($path))
                $streaminfo['animated_preview'] = Config::cdni() . '/' . $path;
        }

        // STREAM HOSTING
        $lasthost = $cache->contains('streamhostinfo') ? $cache->fetch('streamhostinfo') : [];
        $currhost = $twitchApiService->getChannelHostWithInfo(Config::$a['twitch']['id']);
        if(TwitchApiService::checkForHostingChange($lasthost, $currhost) == TwitchApiService::$HOST_NOW_HOSTING){
            $chatIntegration->sendBroadcast(sprintf(
                '%s is now hosting %s at %s',
                Config::$a['meta']['shortName'],
                $currhost['display_name'],
                $currhost['url'])
            );
        }

        // STEAM GO-LIVE ANNOUNCEMENT
        $waslive = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : null;
        if($waslive !== null && $streaminfo !== null && (isset($waslive['live']) && $waslive['live'] == false && $streaminfo['live'] == true)){
            $chatIntegration->sendBroadcast(sprintf(
                '%s is now live!',
                Config::$a['meta']['shortName'])
            );
        }

        $streaminfo['host'] = $currhost;
        $cache->save('streamhostinfo', $currhost);
        $cache->save('streamstatus', $streaminfo);
    }

}