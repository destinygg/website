<?php
namespace Destiny\Reddit;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;

/**
 * @method static RedditAuthHandler instance()
 */
class RedditAuthHandler extends AbstractAuthHandler {

    private $apiBase = 'https://ssl.reddit.com/api/v1';
    private $authBase = 'https://oauth.reddit.com/api/v1';
    public $authProvider = AuthProvider::REDDIT;

    /**
     * @return string
     */
    function getAuthorizationUrl($scope = ['identity'], $claims = ''): string {
        $conf = $this->getAuthProviderConf();
        return "$this->apiBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope' => join(' ', $scope),
                'state' => md5(time() . 'eFdcSA_'),
                'client_id' => $conf['client_id'],
                'redirect_uri' => $conf['redirect_uri']
            ], null, '&');
    }

    /**
     * @throws Exception
     */
    function getToken(array $params): array {
        if (!isset($params['code']) || empty($params['code'])) {
            throw new Exception ('Authentication failed, invalid or empty code.');
        }
        $conf = $this->getAuthProviderConf();
        $client = $this->getHttpClient();
        $response = $client->post("$this->apiBase/access_token", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => 'Basic ' . base64_encode($conf['client_id']. ':' .$conf['client_secret'])
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $conf['client_id'],
                'redirect_uri' => $conf['redirect_uri'],
                'code' => $params['code']
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $data = json_decode((string)$response->getBody(), true);
            if (empty($data) || isset($data['error']) || !isset($data['access_token'])) {
                throw new Exception('Invalid access_token response');
            }
            return $data;
        }
        throw new Exception("Failed to get token response");
    }

    /**
     * @throws Exception
     */
    private function getUserInfo(string $accessToken): array {
        $client = $this->getHttpClient();
        $response = $client->get("$this->authBase/me.json", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "bearer $accessToken"
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string)$response->getBody(), true);
        }
        throw new Exception("Failed to retrieve user info.");
    }

    /**
     * @throws Exception
     */
    function mapTokenResponse(array $token): OAuthResponse {
        $data = $this->getUserInfo($token['access_token']);
        if (empty($data) || !isset($data['id']) || empty($data['id'])) {
            throw new Exception ('Authorization failed, invalid user data');
        }
        return new OAuthResponse([
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'] ?? '',
            'authProvider' => $this->authProvider,
            'username' => $data['name'] ?? '',
            'authId' => (string) $data['id'],
            'authDetail' => $data['name'] ?? '',
            'authEmail' => '',
            'verified' => boolval($data['has_verified_email'] ?? false),
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