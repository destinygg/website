<?php
namespace Destiny\Twitch;

use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

class TwitchAuthHandler implements AuthHandlerInterface {
  
    /**
     * @var string
     */
    protected $authProvider = 'twitch';
    protected $apiBase = 'https://api.twitch.tv/kraken';
    protected $oauthBase = 'https://api.twitch.tv/kraken/oauth2';

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        return "$this->oauthBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope'         => 'user_read',
                'client_id'     => $conf['client_id'],
                'redirect_uri'  => $conf['redirect_uri'],
            ], null, '&');
    }

    /**
     * @param array $params
     * @return string
     * @throws DBALException
     * @throws Exception
     */
    public function authenticate(array $params) {
        if (!isset ($params['code']) || empty ($params['code'])) {
            throw new Exception ('Authentication failed, invalid or empty code.');
        }
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->post("$this->oauthBase/token", [
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
            $data = json_decode((string) $response->getBody(), true);
            if (empty ($data) || isset ($data['error']) || !isset ($data['access_token']))
                throw new Exception ('Invalid access_token response');
            $info = $this->getUserInfo($data['access_token']);
            if($info != null) {
                $auth = $this->getAuthCredentials($params['code'], $info);
                $authHandler = new AuthenticationRedirectionFilter ();
                return $authHandler->execute($auth);
            }
        }
        throw new Exception ( 'Bad response from oauth provider' );
    }

    /**
     * @param string $code
     * @param array $data
     * @return AuthenticationCredentials
     * @throws Exception
     */
    private function getAuthCredentials($code, array $data) {
        if (empty ($data) || !isset ($data ['_id']) || empty ($data ['_id'])) {
            throw new Exception ('Authorization failed, invalid user data');
        }
        if (!isset($data['email']) || empty($data['email']) || !$data['email']) {
            throw new Exception ('You must have a verified email address for your registration to complete successfully.');
        }
        $arr = [];
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['_id'];
        $arr ['authDetail'] = $data ['name'];
        $arr ['username'] = (isset ($data ['display_name']) && !empty ($data ['display_name'])) ? $data ['display_name'] : $data ['name'];
        $arr ['email'] = $data ['email'];
        return new AuthenticationCredentials ($arr);
    }

    /**
     * @param $access_token
     * @return array|null
     */
    private function getUserInfo($access_token){
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/user", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => $conf['client_id'],
                'Authorization' => "OAuth $access_token",
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string) $response->getBody(), true);
        }
        return null;
    }
}