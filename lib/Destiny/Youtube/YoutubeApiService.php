<?php
namespace Destiny\Youtube;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Service;
use Destiny\Common\User\UserAuthService;
use Destiny\Common\Utils\Http;

/**
 * @method static YoutubeApiService instance()
 */
class YouTubeApiService extends Service {
    private $apiBase = 'https://www.googleapis.com/youtube/v3';
    private $provider = AuthProvider::YOUTUBE;

    /**
     * @throws Exception
     */
    public function getChannelsForUserId(int $userId): array {
        $oauthDetails = UserAuthService::instance()->getByUserIdAndProvider($userId, $this->provider);
        if (empty($oauthDetails)) {
            throw Exception("Error getting YT channel IDs because no OAuth details exist for user `$userId`.");
        }

        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/channels", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer {$oauthDetails['accessToken']}"
            ],
            'query' => [
                'part' => 'id,snippet',
                'mine' => 'true'
            ]
        ]);

        if ($response->getStatusCode() !== Http::STATUS_OK) {
            throw Exception("Got a non-200 response when fetching YouTube channels for user `$userId`: `$response->getBody()`.");
        }

        $json = json_decode($response->getBody(), true);
        return $json['items'];
    }
}
