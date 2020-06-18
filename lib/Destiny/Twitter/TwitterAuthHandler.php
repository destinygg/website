<?php
namespace Destiny\Twitter;

use Destiny\Common\Authentication\AbstractAuthHandler;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * @method static TwitterAuthHandler instance()
 */
class TwitterAuthHandler extends AbstractAuthHandler {

    private $authBase = 'https://api.twitter.com/oauth';
    private $apiBase = 'https://api.twitter.com/1.1';
    public $authProvider = AuthProvider::TWITTER;
    public $userProfileBaseUrl = 'https://twitter.com';

    public function createHttpClient(array $params = null): Client {
        $conf = $this->getAuthProviderConf();
        $stack = HandlerStack::create();
        $stack->push(new Oauth1([
            'consumer_key' => $conf['client_id'],
            'consumer_secret' => $conf['client_secret'],
            'callback' => $conf['redirect_uri'],
            'token' => $params['oauth_token'] ?? '',
            'token_secret' => $params['oauth_token_secret'] ?? '',
            'verifier' => $params['oauth_verifier'] ?? ''
        ]));
        return parent::createHttpClient([
            'handler' => $stack,
            'auth' => 'oauth'
        ]);
    }

    /**
     * @throws Exception
     */
    function getAuthorizationUrl($scope = [], $claims = ''): string {
        $conf = $this->getAuthProviderConf();
        $response = $this->getHttpClient()->post("$this->authBase/request_token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => ['oauth_callback' => $conf['redirect_uri']]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $params = $this->extractParams((string)$response->getBody());
            if (!$params['oauth_callback_confirmed'] || $params['oauth_callback_confirmed'] !== 'true') {
                throw new Exception ('The callback was not confirmed by Twitter so we cannot continue.');
            }
            return "$this->authBase/authorize?oauth_token={$params['oauth_token']}";
        }
        throw new Exception ('There was an error communicating with Twitter.');
    }

    /**
     * @throws Exception
     */
    function getToken(array $params): array {
        FilterParams::required($params, 'oauth_token');
        FilterParams::required($params, 'oauth_verifier');
        $response = $this->getHttpClient()->post("$this->authBase/access_token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'oauth_token' => trim($params['oauth_token']),
                'oauth_verifier' => trim($params['oauth_verifier']),
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $body = (string) $response->getBody();
            $data = $this->extractParams($body) ?? [];
            $this->setHttpClient($this->createHttpClient($data)); // TODO hackey
            return $data;
        }
        throw new Exception("Failed to get token response");
    }

    /**
     * @throws Exception
     */
    private function getUserInfo(): array {
        $response = $this->getHttpClient()->get("$this->apiBase/account/verify_credentials.json?include_email=true&include_entities=false&skip_status=true", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'auth' => 'oauth',
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
        $data = $this->getUserInfo();
        FilterParams::required($data, 'id_str');
        return new OAuthResponse([
            'authProvider' => $this->authProvider,
            'accessToken' => $token['oauth_token'],
            'refreshToken' => '',
            'username' => $data['screen_name'] ?? '',
            'authId' => $data['id_str'],
            'authDetail' => $data['screen_name'] ?? '',
            'authEmail' => $data['email'] ?? '',
            'verified' => true //$data['verified'], this is not email verified
        ]);
    }

    /**
     * Extracts and decodes OAuth parameters from the passed string
     *
     * @param string $body the response body from an OAuth flow method
     * @return array the response body safely decoded to an array of key => values
     */
    private function extractParams(string $body): array {
        $kvs = explode('&', $body);
        $decoded = [];
        foreach ($kvs as $kv) {
            $kv = explode('=', $kv, 2);
            $kv[0] = $this->safeDecode($kv[0]);
            $kv[1] = $this->safeDecode($kv[1]);
            $decoded[$kv[0]] = $kv[1];
        }
        return $decoded;
    }

    /**
     * Decodes the string or array from it's URL encoded form
     * If an array is passed each array value will will be decoded.
     *
     * @param mixed $data the scalar or array to decode
     * @return string $data decoded from the URL encoded form
     */
    private function safeDecode(string $data): string {
        if (is_array($data)) {
            return array_map([$this, 'safeDecode'], $data);
        } else if (is_scalar($data)) {
            return rawurldecode($data);
        } else {
            return '';
        }
    }

    /**
     * @param string $refreshToken
     * @return array
     * @throws Exception
     */
    function renewToken(string $refreshToken): array {
        throw new Exception("Not implemented");
        // TODO Implement
    }

}