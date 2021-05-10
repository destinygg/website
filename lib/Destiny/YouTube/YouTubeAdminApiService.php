<?php
namespace Destiny\YouTube;

use Destiny\Common\Application;
use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Exception;

class YouTubeAdminApiService extends AbstractAuthService {
    const CACHE_KEY_UPLOADS_PLAYLIST_ID = 'ytUploadPlaylistId';
    const CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN = 'ytMemberUpdatesToken';

    private $apiBase = 'https://www.googleapis.com/youtube/v3';
    public $provider = AuthProvider::YOUTUBE_BROADCASTER;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = YouTubeBroadcasterAuthHandler::instance();
    }

    public function getMembershipLevels(): array {
        $response = $this->performGet('membershipsLevels', [
            'part' => 'id,snippet'
        ]);

        $json = json_decode($response->getBody(), true);
        return $json['items'];
    }

    public function getAllMemberships(): array {
        $memberships = [];
        $pageToken = null;

        // Keep fetching pages until we don't get a next page token.
        do {
            $response = $this->performGet('members', [
                'part' => 'snippet',
                'mode' => 'all_current',
                'maxResults' => 1000, // The highest value permitted.
                'pageToken' => $pageToken
            ]);

            $json = json_decode($response->getBody(), true);
            $pageToken = $json['nextPageToken'] ?? null;
            $memberships = array_merge($memberships, $json['items']);
        } while (!empty($pageToken));

        return $memberships;
    }

    public function getNewMemberships(): array {
        // Fetching a non-existent value from the cache returns `false`. We
        // check to see if it exists to avoid mistakenly passing `false` into
        // the members request.
        $cache = Application::getNsCache();
        if ($cache->contains(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN)) {
            $pageToken = $cache->fetch(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN);
        } else {
            $pageToken = null;
        }

        try {
            $response = $this->performGet('members', [
                'part' => 'snippet',
                'mode' => 'updates',
                'maxResults' => 1000, // The highest value permitted.
                'pageToken' => $pageToken
            ]);
        } catch (Exception $e) {
            // Clear the cached page token just in case to avoid potential
            // errors on subsequent calls.
            $cache->delete(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN);
            throw $e;
        }

        $json = json_decode($response->getBody(), true);
        $pageToken = $json['nextPageToken'] ?? null;
        if (!empty($pageToken)) {
            $cache->save(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN, $pageToken);
        } else {
            $cache->delete(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN);
        }

        return $json['items'];
    }

    public function getRecentYouTubeVideos(): array {
        $channelId = Config::$a[AuthProvider::YOUTUBE_BROADCASTER]['channelId'];
        if (empty($channelId)) {
            return [];
        }

        $cache = Application::getNsCache();
        $uploadsPlaylistId = $cache->fetch(self::CACHE_KEY_UPLOADS_PLAYLIST_ID . ':' . $channelId);
        if (empty($uploadsPlaylistId)) {
            Log::debug('No uploads playlist ID in cache.');
            $uploadsPlaylistId = $this->getUploadsPlaylistIdForChannel($channelId);
            $cache->save(self::CACHE_KEY_UPLOADS_PLAYLIST_ID . ':' . $channelId, $uploadsPlaylistId);
        }

        if (empty($uploadsPlaylistId)) {
            throw new Exception("No uploads playlist ID found for channel `{$channelId}`.");
        }

        Log::debug("Got ID of uploads playlist: `$uploadsPlaylistId`.");

        $response = $this->performGet('playlistItems', [
            'part' => 'snippet',
            'playlistId' => $uploadsPlaylistId,
            'maxResults' => 50,
        ]);

        Log::debug("Got playlist items: `{$response->getBody()}`.");

        $json = json_decode($response->getBody(), true);

        $videoIds = array_map(function($playlistItem) {
            return $playlistItem['snippet']['resourceId']['videoId'];
        }, $json['items']);

        $videos = $this->getVideos($videoIds);
        return $videos;
    }

    public function getUploadsPlaylistIdForChannel(string $channelId): string {
        $response = $this->performGet('channels', [
            'part' => 'contentDetails',
            'id' => $channelId
        ]);

        $json = json_decode($response->getBody(), true);
        $channels = $json['items'];
        if (count($channels) < 1) {
            throw new Exception("No channel with ID `$channelId` found.");
        }

        return $channels[0]['contentDetails']['relatedPlaylists']['uploads'];
    }

    public function getVideos(array $videoIds): array {
        if (empty($videoIds)) {
            return [];
        }

        $response = $this->performGet('videos', [
            'part' => 'id,liveStreamingDetails,snippet,status',
            'id' => implode(',', $videoIds),
        ]);

        $json = json_decode($response->getBody(), true);
        return $json['items'];
    }

    private function performGet(string $path, array $query) {
        $accessToken = $this->getValidAccessToken();

        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/$path", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ],
            'query' => $query,
            \GuzzleHttp\RequestOptions::HTTP_ERRORS => true
        ]);

        return $response;
    }
}
