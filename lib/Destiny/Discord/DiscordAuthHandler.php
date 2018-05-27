<?php
namespace Destiny\Discord;

use Destiny\Common\Authentication\AuthenticationCredentials;
use Destiny\Common\Authentication\AuthenticationRedirectionFilter;
use Destiny\Common\AuthHandlerInterface;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

class DiscordAuthHandler implements AuthHandlerInterface {

    /**
     * @var string
     */
    protected $authProvider = 'discord';
    protected $apiBase = 'https://discordapp.com/api/v6';
    protected $authBase = 'https://discordapp.com/api/oauth2';

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a ['oauth_providers'] [$this->authProvider];
        return "$this->authBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope' => 'identify email',
                'state' => md5(time() . 'ifC35_'),
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
        $response = $client->post("$this->authBase/token", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => 'Basic ' . base64_encode($conf['client_id'] . ':' . $conf['client_secret'])
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'redirect_uri' => $conf['redirect_uri'],
                'code' => $params['code']
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $data = json_decode((string)$response->getBody(), true);
            if (empty($data) || isset($data['error']) || !isset($data['access_token']))
                throw new Exception('Invalid access_token response');
            $info = $this->getUserInfo($data['access_token']);
            $authCreds = $this->getAuthCredentials($params['code'], $info);
            $authCredHandler = new AuthenticationRedirectionFilter ();
            return $authCredHandler->execute($authCreds);
        }
        throw new Exception ("Bad response from oauth provider: {$response->getStatusCode()}");
    }

    /**
     * @param $token
     * @return array|null
     */
    private function getUserInfo($token) {
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/users/@me", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Authorization' => "Bearer $token"
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            return json_decode((string)$response->getBody(), true);
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
        if (empty ($data) || !isset ($data ['id']) || empty ($data ['id'])) {
            throw new Exception ('Authorization failed, invalid user data');
        }
        if (!isset($data['verified']) || empty($data['verified']) || $data['verified'] != 1) {
            throw new Exception (' You must have a verified email address for your registration to complete successfully.');
        }
        $arr = [];
        $arr ['authProvider'] = $this->authProvider;
        $arr ['authCode'] = $code;
        $arr ['authId'] = $data ['id'];
        $arr ['authDetail'] = $data ['username'];// . '#' . $data ['discriminator'];
        $arr ['username'] = $data ['username'];
        $arr ['email'] = $data ['email'];
        return new AuthenticationCredentials ($arr);
    }

}