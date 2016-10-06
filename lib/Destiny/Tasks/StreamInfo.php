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
        $lasthost = $cache->contains('streamhost') ? $cache->fetch('streamhost') : [];
        $currhost = $twitchApiService->getChannelHost(Config::$a['twitch']['id']);
        if(!empty($currhost) && is_array($currhost) && isset($currhost['host_id']) && isset($currhost['host_login'])){

            unset($currhost['host_id']);
            unset($currhost['host_login']);

            switch (TwitchApiService::checkForHostingChange($lasthost, $currhost)){
                case TwitchApiService::$HOST_NOW_HOSTING:
                    $target = $twitchApiService->getChannel($currhost['target_login']);
                    if (!empty($target) && isset($target['display_name']) && isset($target['url'])) {
                        $chatIntegrationService = ChatIntegrationService::instance();
                        $chatIntegrationService->sendBroadcast(sprintf(
                            '%s is now hosting %s at %s',
                            Config::$a['meta']['shortName'],
                            $target['display_name'],
                            $target['url'])
                        );
                    }
                    $currhost['display_name'] = $target['display_name'];
                    $currhost['url'] = $target['url'];
                    $currhost['preview'] = $target['preview'];
                    $cache->save('streamhost', $currhost);
                    break;
                case TwitchApiService::$HOST_STOPPED:
                    $cache->save('streamhost', $currhost);
                    break;
                case TwitchApiService::$HOST_UNCHANGED:
                    $currhost = $lasthost;
                    break;
            }
            // SAVE THE STATUS AND HOSTING INFO
            $streaminfo['host'] = $currhost;
        } else {
            $streaminfo['host'] = null;
        }

        $cache->save('streamstatus', $streaminfo);
    }

}