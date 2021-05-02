<?php
namespace Destiny\YouTube;

use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Session\Session;
use Destiny\Google\GoogleAuthHandler;

class YouTubeAuthHandler extends GoogleAuthHandler {
private $apiBase = 'https://www.googleapis.com/youtube/v3';
    public $authProvider = AuthProvider::YOUTUBE;

    function getAuthorizationUrl(
        $scope = [
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
            'redirect_uri' => sprintf($conf['redirect_uri'], $this->authProvider)
        ], null, '&');
    }

    /**
     * @throws Exception
     */
    public function mapTokenResponse(array $token): OAuthResponse {
        return new OAuthResponse([
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'] ?? '',
            'authProvider' => $this->authProvider,
            'username' => '',
            'authId' => '',
            'authDetail' => '',
            'authEmail' => '',
            'verified' => true,
        ]);
    }
}
