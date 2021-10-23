<?php
namespace Destiny\Twitch;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Exception\RequestException;

/**
 * TODO validating requests
 * https://dev.twitch.tv/docs/authentication/
 * @method static TwitchAuthHandler instance()
 */
class TwitchAuthHandler extends AbstractAuthHandler {

    const GRANT_TYPE_USER = 'authorization_code';
    const GRANT_TYPE_APP = 'client_credentials';
  
    private $authBase = 'https://id.twitch.tv/oauth2';
    public $authProvider = AuthProvider::TWITCH;
    public $userProfileBaseUrl = 'https://www.twitch.tv';

    public function exchangeCode(array $params): OAuthResponse {
        $params['grant_type'] = self::GRANT_TYPE_USER;
        return $this->mapTokenResponse($this->getToken($params));
    }

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
        FilterParams::required($params, 'grant_type');

        $conf = $this->getAuthProviderConf();
        $client = $this->getHttpClient();

        # Baseline params for both user and app tokens.
        $form_params = [
            'grant_type' => $params['grant_type'],
            'client_id' => $conf['client_id'],
            'client_secret' => $conf['client_secret']
        ];

        if ($params['grant_type'] == self::GRANT_TYPE_USER) {
            FilterParams::required($params, 'code');
            $form_params += [
                'redirect_uri' => $conf['redirect_uri'],
                'code' => $params['code']
            ];
        }

        try {
            $response = $client->post("$this->authBase/token", [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Client-ID' => $conf['client_id']
                ],
                'form_params' => $form_params,
                'http_errors'=> true
            ]);

            $data = json_decode((string)$response->getBody(), true);
            FilterParams::required($data, 'access_token');
            return $data;
        } catch (RequestException $e) {
            throw new Exception('Failed to get Twitch auth token.', $e);
        }
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

    /**
     * Validates a token using the `/validate` OAuth endpoint. A response with
     * status code `200` indicates the token is valid.
     *
     * @see https://dev.twitch.tv/docs/authentication/#validating-requests
     */
    function validateToken(string $accessToken): bool {
        $client = $this->getHttpClient();
        $response = $client->get("$this->authBase/validate", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => 'Bearer ' . $accessToken
            ]
        ]);

        return $response->getStatusCode() == Http::STATUS_OK;
    }
}
