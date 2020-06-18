<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\HttpClient;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use GuzzleHttp\Client;

abstract class AbstractAuthHandler extends Service implements AuthenticationHandler {

    private $httpClient;
    public $authProvider;
    public $userProfileBaseUrl;

    /**
     * @throws Exception
     */
    abstract protected function mapTokenResponse(array $token): OAuthResponse;

    /**
     * @throws Exception
     */
    public function exchangeCode(array $params): OAuthResponse {
        return $this->mapTokenResponse($this->getToken($params));
    }

    public function getHttpClient(array $options = null): Client {
        if ($this->httpClient == null) {
            $this->httpClient = $this->createHttpClient($options);
        }
        return $this->httpClient;
    }

    public function setHttpClient(Client $client) {
        $this->httpClient = $client;
    }

    public function createHttpClient(array $options = null): Client {
        return HttpClient::instance($options);
    }

    public function getAuthProviderId(): string {
        return $this->authProvider;
    }

    public function getAuthProviderConf(): array {
        return Config::$a['oauth_providers'][$this->getAuthProviderId()];
    }

    public function isTokenExpired(array $auth): bool {
        return Date::getDateTimePlusSeconds($auth['createdDate'], intval($auth['expiresIn'] ?? '3600')) < Date::now();
    }

    public function getUserProfileUrl(string $username): string {
        // Assume the authenticator doesn't have profile pages if `$userProfileBaseUrl` is empty.
        return !empty($this->userProfileBaseUrl) ? "$this->userProfileBaseUrl/$username" : '';
    }

}