<?php
namespace Destiny\Youtube;

use Destiny\Common\Application;
use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;

class YouTubeAdminApiService extends AbstractAuthService {
    const CACHE_KEY_UPLOADS_PLAYLIST_ID = 'ytUploadPlaylistId';

    private $apiBase = 'https://www.googleapis.com/youtube/v3';
    public $provider = AuthProvider::YOUTUBE_BROADCASTER;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = YouTubeBroadcasterAuthHandler::instance();
    }

    public function getRecentYouTubeUploads(int $limit = 4) {
        $authResponse = $this->getDefaultAuth();
        $channelId = Config::$a[AuthProvider::YOUTUBE_BROADCASTER]['channelId'];
        if (empty($authResponse)) {
            return null;
        } else if (empty($channelId)) {
            return null;
        }
        $accessToken = $this->getValidAccessToken();

        $cache = Application::getNsCache();
        $uploadsPlaylistId = $cache->fetch(self::CACHE_KEY_UPLOADS_PLAYLIST_ID);
        if (empty($uploadsPlaylistId)) {
            Log::debug('No uploads playlist ID in cache.');
            $uploadsPlaylistId = $this->getUploadsPlaylistIdForChannel($channelId, $accessToken);
            $cache->save(self::CACHE_KEY_UPLOADS_PLAYLIST_ID, $uploadsPlaylistId);
        }

        if (empty($uploadsPlaylistId)) {
            Log::warning('No uploads playlist ID found.');
            return null;
        }

        Log::debug("Got ID of uploads playlist: `$uploadsPlaylistId`.");

        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/playlistItems", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ],
            'query' => [
                'part' => 'snippet',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => $limit
            ]
        ]);

        if ($response->getStatusCode() !== Http::STATUS_OK) {
            return null;
        }

        Log::debug("Got playlist items: `{$response->getBody()}`.");
        $json = json_decode($response->getBody(), true);
        foreach ($json['items'] as $video) {
            $video['snippet']['publishedAt'] = Date::getDateTime($video['snippet']['publishedAt']);
        }

        return $json;
    }

    private function getUploadsPlaylistIdForChannel(string $channelId, string $accessToken): ?string {
        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/channels", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ],
            'query' => [
                'part' => 'contentDetails',
                'id' => $channelId
            ]
        ]);
        if ($response->getStatusCode() !== Http::STATUS_OK) {
            return null;
        }

        $json = json_decode($response->getBody(), true);
        $channels = $json['items'];
        if (count($channels) < 1) {
            return null;
        }

        return $channels[0]['contentDetails']['relatedPlaylists']['uploads'];
    }
}
