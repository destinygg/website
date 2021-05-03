<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Cron\TaskInterface;
use Destiny\Common\Log;
use Destiny\Common\Images\ImageDownloadUtil;
use Destiny\Common\Utils\Date;
use Destiny\YouTube\YouTubeAdminApiService;

/**
 * @Schedule(frequency=1, period="minute")
 */
class YouTubeTasks implements TaskInterface {
    const RECENT_YOUTUBE_UPLOADS_CACHE_KEY = 'youtubeplaylist';
    const RECENT_YOUTUBE_LIVESTREAM_VODS_CACHE_KEY = 'youtubevods';
    const YOUTUBE_LIVESTREAM_STATUS_CACHE_KEY = 'ytstreamstatus';
    const MAX_RECENT_VIDEO_UPLOADS = 4;
    const MAX_RECENT_LIVESTREAM_VODS = 4;

    public function execute() {
        try {
            $videos = YouTubeAdminApiService::instance()->getRecentYouTubeUploads();
            $this->updateRecentVideoUploads($videos);
            $this->updateRecentLivestreamVODs($videos);
            $this->updateLivestreamStatus($videos);
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

        $normalizedUploads = [];
        for ($i = 0; $i < count($recentPublicVideoUploads); $i++) {
            $video = $recentPublicVideoUploads[$i];
            $path = ImageDownloadUtil::download($video['snippet']['thumbnails']['high']['url']);
            $normalizedUploads[] = [
                'videoUrl' => $this->urlForVideo($video),
                'title' => $video['snippet']['title'],
                'thumbnailUrl' => !empty($path) ? Config::cdni() . '/' . $path : null,
            ];
        }

        $cache = Application::getNsCache();
        $cache->save(self::RECENT_YOUTUBE_UPLOADS_CACHE_KEY, $normalizedUploads);
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

        $normalizedBroadcasts = [];
        for ($i = 0; $i < count($completedBroadcasts); $i++) {
            $broadcast = $completedBroadcasts[$i];
            $path = ImageDownloadUtil::download($broadcast['snippet']['thumbnails']['high']['url']);
            $normalizedBroadcasts[] = [
                'videoUrl' => $this->urlForVideo($broadcast),
                'title' => $broadcast['snippet']['title'],
                'thumbnailUrl' => !empty($path) ? Config::cdni() . '/' . $path : null,
                'startTime' => $broadcast['liveStreamingDetails']['actualStartTime'],
            ];
        }

        $cache = Application::getNsCache();
        $cache->save(self::RECENT_YOUTUBE_LIVESTREAM_VODS_CACHE_KEY, $normalizedBroadcasts);
    }

    public function updateLivestreamStatus(array $videos) {
        // Get the latest broadcast.
        $currentBroadcast = null;
        foreach ($videos as $video) {
            if (!empty($video['liveStreamingDetails'])) {
                $currentBroadcast = $video;
                break; 
            }
        }

        if (empty($currentBroadcast)) {
            Log::warning('No YouTube livestreams found.');
            return;
        }

        $actualStartTime = $currentBroadcast['snippet']['actualStartTime'] ?? null;
        $startTime = !empty($actualStartTime) ? Date::getDateTime($actualStartTime) : null;

        $actualEndTime = $currentBroadcast['snippet']['actualEndTime'] ?? null;
        $endTime = !empty($actualEndTime) ? Date::getDateTime($actualEndTime) : null;

        $livestreamStatus = [
            'live' => !empty($startTime) && empty($endTime),
            'game' => null,
            'status_text' => $currentBroadcast['snippet']['title'],
            'preview' => $currentBroadcast['snippet']['thumbnails']['medium']['url'],
            'started_at' => !empty($startTime) ? $startTime->format(Date::FORMAT) : null,
            'ended_at' => !empty($endTime) ? $endTime->format(Date::FORMAT) : null,
            'duration' => !empty($startTime) ? time() - $startTime->getTimestamp() : null,
            'viewers' => $currentBroadcast['liveStreamingDetails']['concurrentViewers'],
        ];

        $cache = Application::getNsCache();
        $cache->save(self::YOUTUBE_LIVESTREAM_STATUS_CACHE_KEY, $livestreamStatus);
    }

    private function urlForVideo(array $video): string {
        return "https://www.youtube.com/watch?v={$video['id']}";
    }
}
