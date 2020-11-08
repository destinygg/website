<?php
namespace Destiny\Google;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;

class YouTubeAuthHandler extends GoogleAuthHandler {
    private $apiBase = 'https://www.googleapis.com/youtube/v3';
    public $authProvider = AuthProvider::YOUTUBE;

    function getAuthorizationUrl(
        $scope = [
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.channel-memberships.creator',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube.readonly'
        ],
        $claims = ''
    ): string {
        $conf = $this->getAuthProviderConf();
        return "$this->authBase/auth?" . http_build_query([
            'response_type' => 'code',
            'scope' => join(' ', $scope),
            'state' => 'security_token=' . Session::getSessionId(),
            'client_id' => $conf['client_id'],
            'redirect_uri' => sprintf($conf['redirect_uri'], $this->authProvider),
            'access_type' => 'offline'
        ], null, '&');
    }

    /**
     * @throws Exception
     */
    private function getUserChannelIds(string $accessToken): array {
        $client = $this->getHttpClient();
        $response = $client->get("$this->apiBase/channels", [
            'query' => [
                'part' => 'snippet,id,statistics',
                'mine' => true
            ],
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ]
        ]);

        if ($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode($response->getBody(), true);
        }

        throw new Exception('Error getting YouTube channels.');
    }

    /**
     * @throws Exception
     */
    public function mapTokenResponse(array $token): OAuthResponse {
        $data = $this->getUserChannelIds($token['access_token']);
        FilterParams::required($data, 'items');

        if (count($data['items']) < 1) {
            throw new Exception('No YouTube channels exist.');
        }

        // Sort the channels by decreasing sub count.
        $channels = $data['items'];
        usort($channels, function($a, $b) {
            $aSubs = $a['statistics']['subscriberCount'];
            $bSubs = $b['statistics']['subscriberCount'];

            if ($aSubs === $bSubs) {
                return 0;
            }
            return $aSubs < $bSubs ? 1 : -1;
        });

        // Get the channel with the most subs.
        $firstChannel = $channels[0];
        return new OAuthResponse([
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'],
            'authProvider' => $this->authProvider,
            'username' => $firstChannel['snippet']['title'],
            'authId' => $firstChannel['id'],
            'authDetail' => $firstChannel['snippet']['title'],
            'authEmail' => '',
            'verified' => true,
        ]);
    }
}
