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

    public function execute() {
        $cache = Application::instance()->getCacheDriver();
        $twitchApiService = TwitchApiService::instance();

        // STREAM STATUS
        $streaminfo = $twitchApiService->getStreamInfo(Config::$a ['twitch']['user']);
        if (!empty($streaminfo)) {
            $path = ImageDownload::download($streaminfo['preview'], Config::$a['images']['path'], true);
            if (!empty($path))
                $streaminfo['preview'] = Config::cdn() . '/img/' . $path;
            $path = ImageDownload::download($streaminfo['animated_preview'], Config::$a['images']['path'], true);
            if (!empty($path))
                $streaminfo['animated_preview'] = Config::cdn() . '/img/' . $path;
        }

        // STREAM HOSTING
        $lasthost = $cache->contains('streamhostinfo') ? $cache->fetch('streamhostinfo') : [];
        $currhost = $twitchApiService->getChannelHostWithInfo(Config::$a['twitch']['id']);

        switch (TwitchApiService::checkForHostingChange($lasthost, $currhost)){
            case TwitchApiService::$HOST_NOW_HOSTING:
                $chatIntegrationService = ChatIntegrationService::instance();
                $chatIntegrationService->sendBroadcast(sprintf(
                    '%s is now hosting %s at %s',
                    Config::$a['meta']['shortName'],
                    $currhost['display_name'],
                    $currhost['url'])
                );
                break;
            case TwitchApiService::$HOST_UNCHANGED:
            case TwitchApiService::$HOST_STOPPED:
                break;
        }

        $streaminfo['host'] = $currhost;
        $cache->save('streamhostinfo', $currhost);
        $cache->save('streamstatus', $streaminfo);
    }

}