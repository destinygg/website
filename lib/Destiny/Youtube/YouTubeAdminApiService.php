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
    const CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN = 'ytMemberUpdatesToken';

    private $apiBase = 'https://www.googleapis.com/youtube/v3';
    public $provider = AuthProvider::YOUTUBE_BROADCASTER;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = YouTubeBroadcasterAuthHandler::instance();
    }

    public function getMembershipLevels() {
        $accessToken = $this->getValidAccessToken();

        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/membershipsLevels", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ],
            'query' => [
                'part' => 'id,snippet'
            ]
        ]);

        $json = json_decode($response->getBody(), true);
        $membershipLevels = $json['items'];
        if (count($membershipLevels) < 1) {
            return null;
        }

        return $membershipLevels;
    }

    public function getAllMemberships() {
        $accessToken = $this->getValidAccessToken();

        $client = HttpClient::instance();
        $memberships = [];
        $pageToken = null;
        do {
            $response = $client->get("$this->apiBase/members", [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Authorization' => "Bearer $accessToken"
                ],
                'query' => [
                    'part' => 'snippet',
                    'mode' => 'all_current',
                    'maxResults' => 1000,
                    'pageToken' => $pageToken
                ]
            ]);

            if ($response->getStatusCode() !== Http::STATUS_OK) {
                return null;
            }

            $json = json_decode($response->getBody(), true);
            $pageToken = $json['nextPageToken'] ?? null;
            $memberships = array_merge($memberships, $json['items']);
        } while (!empty($pageToken));

        return $memberships;
    }

    public function getNewMemberships() {
        $accessToken = $this->getValidAccessToken();

        $cache = Application::getNsCache();
        if ($cache->contains(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN)) {
            $pageToken = $cache->fetch(self::CACHE_KEY_MEMBERSHIP_UPDATES_PAGE_TOKEN);
        } else {
            $pageToken = null;
        }

        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/members", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ],
            'query' => [
                'part' => 'snippet',
                'mode' => 'updates',
                'maxResults' => 1000,
                'pageToken' => $pageToken
            ]
        ]);

        if ($response->getStatusCode() !== Http::STATUS_OK) {
            return null;
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
