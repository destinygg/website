<?php
namespace Destiny\Google;

use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Config;
use Destiny\Common\Session;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

class GoogleAuthHandler implements AuthHandlerInterface {
    
    /**
     * @var string
     */
    protected $authProvider = 'google';
    protected $domain = 'https://accounts.google.com/o/oauth2';

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        return "$this->domain/auth?" . http_build_query([
                'response_type' => 'code',
                'scope' => 'openid email',
                'state' => 'security_token=' . Session::getSessionId(),
                'client_id' => $conf ['client_id'],
                'redirect_uri' => sprintf(Config::$a ['oauth_providers']['google']['redirect_uri'], $this->authProvider)
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
        $response = $client->post("$this->domain/token", [
            'headers' => ['User-Agent' => Config::userAgent()],
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
            if (empty($data) || isset($data['error']) || !isset($data['access_token']))
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
     *
     * @throws Exception
     */
    private function getAuthCredentials($code, array $data) {
        if (empty ( $data ) || ! isset ( $data ['id'] ) || empty ( $data ['id'] )) {
            throw new Exception ( 'Authorization failed, invalid user data' );
        }
        $arr = [];
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['id'];
        $arr ['authDetail'] = $data ['email'];
        $arr ['username'] = (isset ( $data ['hd'] )) ? $data ['hd'] : '';
        $arr ['email'] = $data ['email'];
        return new AuthenticationCredentials ( $arr );
    }

    /**
     * @param $access_token
     * @return array|null
     */
    private function getUserInfo($access_token){
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => ['access_token' => $access_token]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string) $response->getBody(), true);
        }
        return null;
    }
}