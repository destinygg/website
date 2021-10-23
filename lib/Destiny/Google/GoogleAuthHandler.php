<?php
namespace Destiny\Google;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Exception\RequestException;

/**
 * @method static GoogleAuthHandler instance()
 */
class GoogleAuthHandler extends AbstractAuthHandler {

    protected $authBase = 'https://accounts.google.com/o/oauth2';
    private $apiBase = 'https://www.googleapis.com/oauth2/v2';
    public $authProvider = AuthProvider::GOOGLE;

    function getAuthorizationUrl($scope = ['openid', 'email', 'profile'], $claims = ''): string {
        $conf = $this->getAuthProviderConf();
        return "$this->authBase/auth?" . http_build_query([
            'response_type' => 'code',
            'scope' => join(' ', $scope),
            'state' => 'security_token=' . Session::getSessionId(),
            'client_id' => $conf ['client_id'],
            'redirect_uri' => sprintf($conf['redirect_uri'], $this->authProvider)
        ], null, '&');
    }

    /**
     * @throws Exception
     */
    function getToken(array $params): array {
        FilterParams::required($params, 'code');
        $conf = $this->getAuthProviderConf();
        $client = $this->getHttpClient();

        try {
            $response = $client->post("$this->authBase/token", [
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

            // TODO use provided JWT id_token instead of getting user info later
            $data = json_decode((string) $response->getBody(), true);
            FilterParams::required($data, 'access_token');
            return $data;
        } catch (RequestException $e) {
            throw new Exception('Failed to get Google auth token.', $e);
        }
    }

    /**
     * @throws Exception
     */
    private function getUserInfo(string $accessToken): array {
        $client = $this->getHttpClient();
        $response = $client->get("$this->apiBase/userinfo", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $accessToken"
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $info = json_decode((string)$response->getBody(), true);
            if (empty($info) ) {
                throw new Exception ('Invalid user info response');
            }
            return $info;
        }
        throw new Exception ('Invalid user info response');
    }

    /**
     * @throws Exception
     */
    function mapTokenResponse(array $token): OAuthResponse {
        $data = $this->getUserInfo($token['access_token']);
        FilterParams::required($data, 'id');
        return new OAuthResponse([
            'accessToken' => $token['access_token'],
            'refreshToken' => $token['refresh_token'] ?? '',
            'authProvider' => $this->authProvider,
            'username' => '',
            'authId' => (string) $data['id'],
            'authDetail' => $data['hd'] ?? '',
            'authEmail' => $data['email'] ?? '',
            'verified' => boolval($data['verified_email'] ?? true),
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
