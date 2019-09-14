<?php
namespace Destiny\Twitch;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;

/**
 * TODO validating requests
 * https://dev.twitch.tv/docs/authentication/
 * @method static TwitchAuthHandler instance()
 */
class TwitchAuthHandler extends AbstractAuthHandler {
  
    private $authBase = 'https://id.twitch.tv/oauth2';
    public $authProvider = AuthProvider::TWITCH;

    public function getAuthorizationUrl($scope = ['openid', 'user:read:email'], $claims = '{"userinfo":{"picture":null, "email":null, "email_verified":null, "preferred_username": null}}'): string {
        $conf = $this->getAuthProviderConf();
        return "$this->authBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope'         => join(' ', $scope),
                'claims'        => $claims,
                'force_verify'  => true,
                'client_id'     => $conf['client_id'],
                'redirect_uri'  => $conf['redirect_uri'],
            ], null, '&');
    }

    /**
     * @throws Exception
     */
    function getToken(array $params): array {
        FilterParams::required($params, 'code');
        $conf = $this->getAuthProviderConf();
        $client = $this->getHttpClient();
        $response = $client->post("$this->authBase/token", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => $conf['client_id']
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'redirect_uri' => $conf['redirect_uri'],
                'code' => $params['code']
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            $data = json_decode((string)$response->getBody(), true);
            FilterParams::required($data, 'access_token');
            return $data;
        }
        throw new Exception ('Failed to get token response');
    }

    /**
     * @throws Exception
     */
    private function getUserInfo(string $accessToken): array {
        $client = $this->getHttpClient();
        $response = $client->get("$this->authBase/userinfo", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $info = json_decode((string) $response->getBody(), true);
            if (empty($info)) {
                throw new Exception ('Invalid user_info response');
            }
            return $info;
        }
        throw new Exception("Failed to retrieve user info.");
    }

    /**
     * @throws Exception
     */
    function mapTokenResponse(array $token): OAuthResponse {
        $data = $this->getUserInfo($token['access_token']);
        FilterParams::required($data, 'preferred_username');
        return new OAuthResponse([
            'authProvider' => $this->authProvider,
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'] ?? '',
            'username' => $data['preferred_username'],
            'authId' => (string) $data['sub'],
            'authDetail' => $data['preferred_username'],
            'authEmail' => $data['email'] ?? '',
            'verified' => boolval($data['email_verified'] ?? false),
        ]);
    }

    /**
     * @throws Exception
     */
    function renewToken(string $refreshToken): array {
        throw new Exception("Not implemented");
        // TODO Implement
    }

}