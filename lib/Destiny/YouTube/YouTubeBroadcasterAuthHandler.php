<?php
namespace Destiny\YouTube;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;

class YouTubeBroadcasterAuthHandler extends YouTubeAuthHandler {
    public $authProvider = AuthProvider::YOUTUBE_BROADCASTER;

    function getAuthorizationUrl(
        $scope = [
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/youtube.readonly'
        ],
        $claims = ''
    ): string {
        if (Config::$a[$this->authProvider]['sync_memberships']) {
            $scope[] = 'https://www.googleapis.com/auth/youtube.channel-memberships.creator';
        }

        $conf = $this->getAuthProviderConf();
        return "$this->authBase/auth?" . http_build_query([
            'response_type' => 'code',
            'scope' => join(' ', $scope),
            'state' => 'security_token=' . Session::getSessionId(),
            'client_id' => $conf['client_id'],
            'redirect_uri' => sprintf($conf['redirect_uri'], $this->authProvider),
            'access_type' => 'offline',
            'include_granted_scopes' => 'true'
        ], null, '&');
    }

    /**
     * @throws \Exception
     */
    public function renewToken(string $refreshToken): array {
        $conf = $this->getAuthProviderConf();
        $response = $this->getHttpClient()->post("$this->authBase/token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'refresh_token' => $refreshToken
            ],
            'http_errors' => true,
        ]);

        $data = json_decode($response->getBody(), true);
        FilterParams::required($data, 'access_token');
        return $data;
    }
}
