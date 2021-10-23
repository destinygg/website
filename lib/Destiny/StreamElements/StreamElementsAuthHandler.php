<?php
namespace Destiny\StreamElements;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Exception\RequestException;

/**
 * @method static StreamElementsAuthHandler instance()
 */
class StreamElementsAuthHandler extends AbstractAuthHandler {

    private $authBase = 'https://api.streamelements.com/oauth2';
    public $authProvider = AuthProvider::STREAMELEMENTS;

    function getAuthorizationUrl($scope = [], $claims = ''): string {
        $conf = $this->getAuthProviderConf();
        return "$this->authBase/authorize?" . http_build_query([
            'response_type' => 'code',
            'client_id'     => $conf['client_id'],
            'redirect_uri'  => $conf['redirect_uri']
        ], null, '&');
    }

    /**
     * @throws Exception
     */
    function getToken(array $params): array {
        FilterParams::required($params, 'code');
        $conf = $this->getAuthProviderConf();
        try {
            $response = $this->getHttpClient()->post("$this->authBase/token", [
                'headers' => ['User-Agent' => Config::userAgent()],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $conf['client_id'],
                    'client_secret' => $conf['client_secret'],
                    'redirect_uri' => $conf['redirect_uri'],
                    'code' => $params['code']
                ],
                'http_errors'=> true
            ]);

            $data = json_decode((string)$response->getBody(), true);
            FilterParams::required($data, 'access_token');
            FilterParams::declared($data, 'refresh_token');
            return $data;
        } catch (RequestException $e) {
            throw new Exception('Failed to get StreamElements auth token.', $e);
        }
    }

    /**
     * @throws \Exception
     */
    function renewToken(string $refreshToken): array {
        $conf = $this->getAuthProviderConf();
        $response = $this->getHttpClient()->post("$this->authBase/token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'redirect_uri' => $conf['redirect_uri'],
                'refresh_token' => $refreshToken
            ],
            'http_errors' => true,
        ]);

        $data = json_decode((string)$response->getBody(), true);
        FilterParams::required($data, 'access_token');
        return $data;
    }

    /**
     * @throws Exception
     */
    protected function mapTokenResponse(array $token): OAuthResponse {
        $data = $this->getUserInfo($token['access_token']);
        FilterParams::required($data, 'channel_id');
        return new OAuthResponse([
            'authProvider' => $this->authProvider,
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'] ?? '',
            'username' => '',
            'authId' => (string) $data['channel_id'],
            'authDetail' => (string) $data['client_id'],
            'authEmail' => '',
            'verified' => true,
        ]);
    }

    /**
     * @throws Exception
     */
    public function getUserInfo(string $accessToken): array {
        $client = $this->getHttpClient();
        $response = $client->get("$this->authBase/validate", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "OAuth $accessToken",
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string)$response->getBody(), true);
        }
        throw new Exception("Failed to retrieve user info.");
    }
}
