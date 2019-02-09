<?php
namespace Destiny\Twitter;

use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;

class TwitterAuthHandler implements AuthHandlerInterface {

    /**
     * @var string
     */
    protected $authProvider = 'twitter';

    /**
     * @return string
     * @throws Exception
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        $service = TwitterApiService::instance();
        $client = $service->getOAuth1Client($conf);
        $response = $client->post("$service->oauthBase/request_token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'oauth_callback' => $conf['redirect_uri']
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $params = $service->extract_params((string)$response->getBody());
            if (!$params['oauth_callback_confirmed'] && $params['oauth_callback_confirmed'] !== 'true') {
                throw new Exception ('The callback was not confirmed by Twitter so we cannot continue.');
            }
            return "$service->oauthBase/authorize?oauth_token={$params['oauth_token']}";
        }
        throw new Exception ('There was an error communicating with Twitter.');
    }

    /**
     * @param array $params
     * @return string
     * @throws Exception
     *
     * @throws DBALException
     */
    public function authenticate(array $params) {
        if ((!isset ($params ['oauth_token']) || empty ($params ['oauth_token'])) || !isset ($params ['oauth_verifier']) || empty ($params ['oauth_verifier'])) {
            throw new Exception ('Authentication failed');
        }
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        $service = TwitterApiService::instance();
        $client = $service->getOAuth1Client($conf);
        $response = $client->post("$service->oauthBase/access_token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'oauth_token' => trim($params ['oauth_token']),
                'oauth_verifier' => trim($params ['oauth_verifier']),
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $data = $service->extract_params((string)$response->getBody());
            $auth = $this->mapInfoToAuthCredentials($data['oauth_token'], $data);
            $authHandler = new AuthenticationRedirectionFilter($auth);
            return $authHandler->execute();
        }
        throw new Exception ('Failed to retrieve user data');
    }

    /**
     * @param string $code
     * @param array $data
     * @return AuthenticationCredentials
     * @throws Exception
     */
    public function mapInfoToAuthCredentials($code, array $data) {
        if (empty ($data) || !isset ($data['user_id']) || empty ($data['user_id'])) {
            throw new Exception ('Authorization failed, invalid user data');
        }
        $arr = [];
        $arr ['username'] = $data ['screen_name'];
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['user_id'];
        $arr ['authDetail'] = $data ['screen_name'];
        $arr ['authEmail'] = '';
        return new AuthenticationCredentials ($arr);
    }
}