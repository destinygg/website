<?php
namespace Destiny\Reddit;

use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Exception;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Config;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

class RedditAuthHandler implements AuthHandlerInterface {
    
    /**
     * @var string
     */
    protected $authProvider = 'reddit';
    protected $apiBase = 'https://ssl.reddit.com/api/v1';
    protected $authBase = 'https://oauth.reddit.com/api/v1';

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        return "$this->apiBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope' => 'identity',
                'state' => md5(time() . 'eFdcSA_'),
                'client_id' => $conf['client_id'],
                'redirect_uri' => $conf['redirect_uri']
            ], null, '&');
    }

    /**
     * @param array $params
     * @return string
     *
     * @throws DBALException
     * @throws Exception
     */
    public function authenticate(array $params) {
        if (!isset ($params['code']) || empty ($params['code'])) {
            throw new Exception ('Authentication failed, invalid or empty code.');
        }
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
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
            $data = json_decode((string) $response->getBody(), true);
            if (empty($data) || isset($data['error']) || !isset($data['access_token']))
                throw new Exception('Invalid access_token response');
            $info = $this->getUserInfo($data['access_token']);
            $authCreds = $this->getAuthCredentials($params['code'], $info);
            $authCredHandler = new AuthenticationRedirectionFilter ();
            return $authCredHandler->execute($authCreds);
        }
        throw new Exception ( "Bad response from oauth provider: {$response->getStatusCode()}" );
    }

    /**
     * @param $token
     * @return array|null
     */
    private function getUserInfo($token){
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->get("$this->authBase/me.json", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "bearer $token"
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string) $response->getBody(), true);
        }
        return null;
    }

    /**
     * @param string $code
     * @param array $data
     * @return AuthenticationCredentials
     * @throws Exception
     */
    private function getAuthCredentials($code, array $data) {
        if (empty ( $data ) || ! isset ( $data ['id'] ) || empty ( $data ['id'] )) {
            throw new Exception ( 'Authorization failed, invalid user data' );
        }

        if(!isset($data['has_verified_email']) || empty($data['has_verified_email']) || $data['has_verified_email'] != 1){
            throw new Exception ( 'You must have a verified email address for your registration to complete successfully.' );
        }

        $arr = [];
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['id'];
        $arr ['authDetail'] = $data ['name'];
        $arr ['username'] = $data ['name'];
        $arr ['email'] = '';
        return new AuthenticationCredentials ( $arr );
    }
}