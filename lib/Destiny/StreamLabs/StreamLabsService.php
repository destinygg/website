<?php
namespace Destiny\StreamLabs;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;

/**
 * @method static StreamLabsService instance()
 */
class StreamLabsService extends Service {

    /**
     * @var string
     */
    public $authProvider = 'streamlabs';
    private $apiBase = 'https://streamlabs.com/api/v1.0';
    private $auth = null;
    private $default = null;

    /*
    private $alert = [
        'access_token' => '',
        'type' => '',
        'message' => '',
        'image_href' => '',
        'sound_href' => '',
        'special_text_color' => ''
    ];
    private $donation = [
        'access_token' => '',
        'name' => '',
        'message' => '',
        'identifier' => '',
        'amount' => '',
        'currency' => ''
    ];
    */

    /**
     * @return StreamLabsService
     */
    public static function withAuth() {
        $instance = self::instance();
        $instance->setAuth($instance->getDefaultAuth());
        return $instance;
    }

    /**
     * @return array|null
     */
    public function getDefaultAuth() {
        try {
            if ($this->default == null) {
                $auth = UserService::instance()->getUserAuthProfile(
                    Config::$a['streamlabs']['default_user'],
                    'streamlabs'
                );
                if (!empty($auth)) {
                    $this->default = $auth;
                }
            }
        } catch (\Exception $e) {
            Log::error(new Exception("Error getting default auth profile.", $e));
        }
        return $this->default;
    }

    /**
     * @param array $auth
     */
    public function setAuth($auth){
        $this->auth = is_array($auth) && !empty($auth) ? $auth : null;
    }

    /**
     * @param array $args
     * @param array|null $auth
     * @return null|\Psr\Http\Message\ResponseInterface
     *
     * @throws DBALException
     */
    public function sendAlert(array $args, array $auth = null){
        if($auth === null) {
            $auth = $this->auth;
        }
        $token = $this->getFreshValidToken($auth);
        if (!empty($token)) {
            $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
            return $client->post("$this->apiBase/alerts", [
                'headers' => ['User-Agent' => Config::userAgent()],
                'form_params' => array_merge($args, ['access_token'=> $token])]
            );
        }
        return null;
    }

    /**
     * @param array $args
     * @param array $auth
     * @return null|\Psr\Http\Message\ResponseInterface
     *
     * @throws DBALException
     */
    public function sendDonation(array $args, array $auth = null){
        if($auth === null) {
            $auth = $this->auth;
        }
        $token = $this->getFreshValidToken($auth);
        if (!empty($token)) {
            $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
            return $client->post("$this->apiBase/donations", [
                'headers' => ['User-Agent' => Config::userAgent()],
                'form_params' => array_merge($args, ['access_token'=> $token])
            ]);
        }
        return null;
    }

    /**
     * @param array $auth
     * @return string
     *
     * @throws DBALException
     */
    private function renewToken(array $auth){
        $token = $auth['authCode'];
        $conf = Config::$a['oauth_providers'][$this->authProvider];
        $userService = UserService::instance();
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->post("$this->apiBase/token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'redirect_uri'  => $conf['redirect_uri'],
                'refresh_token' => $auth['refreshToken']
            ]
        ]);
        if(!empty($response) && $response->getStatusCode() == Http::STATUS_OK){
            $data = json_decode((string) $response->getBody(), true);
            $userService->updateUserAuthProfile($auth['userId'], $this->authProvider, [
                'refreshToken'  => $data['refresh_token'],
                'authCode'      => $data['access_token'],
                'createdDate'   => Date::getDateTime('NOW')->format('Y-m-d H:i:s'),
                'modifiedDate'  => Date::getDateTime('NOW')->format('Y-m-d H:i:s')
            ]);
            $token = $data['access_token'];
        }
        return $token;
    }

    /**
     * @param array $auth
     * @return string
     *
     * @throws DBALException
     */
    private function getFreshValidToken(array $auth){
        return Date::getDateTime($auth['createdDate'])->getTimestamp() + 3600 < Date::getDateTime()->getTimestamp() ? $this->renewToken($auth) : $auth['authCode'];
    }

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $conf = Config::$a['oauth_providers'][$this->authProvider];
        return "$this->apiBase/authorize?" . http_build_query([
                'response_type' => 'code',
                'scope'         => 'alerts.create donations.create',
                'client_id'     => $conf['client_id'],
                'redirect_uri'  => $conf['redirect_uri']
            ], null, '&');
    }

    /**
     * @param $code
     * @return array
     * @throws Exception
     */
    public function authenticate($code) {
        $conf = Config::$a['oauth_providers'][$this->authProvider];
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->post("$this->apiBase/token", [
            'headers' => ['User-Agent' => Config::userAgent()],
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $conf['client_id'],
                'client_secret' => $conf['client_secret'],
                'redirect_uri' => $conf['redirect_uri'],
                'code' => $code
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            $data = json_decode((string) $response->getBody(), true);
            if (isset($data['access_token']) && isset($data['refresh_token'])){
                return [
                    'access_token'  => $data['access_token'],
                    'refresh_token' => $data['refresh_token']
                ];
            }
        }
        throw new Exception ( 'Bad response from streamlabs' );
    }

}