<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Log;
use Destiny\Common\Images\ImageDownloadUtil;
use Destiny\YouTube\YouTubeAdminApiService;

/**
 * @Schedule(frequency=1, period="minute")
 */
class YouTubeTasks implements TaskInterface {
    const RECENT_YOUTUBE_UPLOADS_CACHE_KEY = 'youtubeplaylist';
    const RECENT_YOUTUBE_LIVESTREAM_VODS_CACHE_KEY = 'youtubevods';
    const MAX_RECENT_VIDEO_UPLOADS = 4;
    const MAX_RECENT_LIVESTREAM_VODS = 4;

    public function execute() {
        try {
            $videos = YouTubeAdminApiService::instance()->getRecentYouTubeUploads();
            $this->updateRecentVideoUploads($videos);
            $this->updateRecentLivestreamVODs($videos);
        } catch (Exception $e) {
            Log::error("Fetching recent YouTube uploads failed. {$e->getMessage()}");
        }
    }

    public function updateRecentVideoUploads(array $videos) {
        // Filter out non-public videos and live broadcasts.
        $recentPublicVideoUploads = array_filter($videos, function($video) {
            return $video['status']['privacyStatus'] === 'public' && empty($video['liveStreamingDetails']);
        });
        $recentPublicVideoUploads = array_slice($recentPublicVideoUploads, 0, self::MAX_RECENT_VIDEO_UPLOADS);

        for ($i = 0; $i < count($recentPublicVideoUploads); $i++) {
            $path = ImageDownloadUtil::download($recentPublicVideoUploads[$i]['snippet']['thumbnails']['high']['url']);
            if (!empty($path)) {
                $recentPublicVideoUploads[$i]['snippet']['thumbnails']['high']['url'] = Config::cdni() . '/' . $path;
            }
        }

        $cache = Application::getNsCache();
        $cache->save(self::RECENT_YOUTUBE_UPLOADS_CACHE_KEY, $recentPublicVideoUploads);
    }

    public function updateRecentLivestreamVODs(array $videos) {
        // Filter out anything that isn't a completed broadcast. We don't have
        // access to a broadcast's `lifeCycleStatus` to check for completion
        // directly, but can simply check if an `actualEndTime` exists instead.
        // If an `actualEndTime` exists, the broadcast is complete.
        $completedBroadcasts = array_filter($videos, function($video) {
            return !empty($video['liveStreamingDetails']['actualEndTime']);
        });
        $completedBroadcasts = array_slice($completedBroadcasts, 0, self::MAX_RECENT_LIVESTREAM_VODS);

        for ($i = 0; $i < count($completedBroadcasts); $i++) {
            $path = ImageDownloadUtil::download($completedBroadcasts[$i]['snippet']['thumbnails']['high']['url']);
            if (!empty($path)) {
                $completedBroadcasts[$i]['snippet']['thumbnails']['high']['url'] = Config::cdni() . '/' . $path;
            }
        }

        $cache = Application::getNsCache();
        $cache->save(self::RECENT_YOUTUBE_LIVESTREAM_VODS_CACHE_KEY, $completedBroadcasts);
    }
}
