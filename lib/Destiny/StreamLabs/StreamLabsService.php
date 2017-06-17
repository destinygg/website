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
use OAuth2;
use GuzzleHttp;

class StreamLabsService extends Service {

    /**
     * @var string
     */
    public $authProvider = 'streamlabs';
    private $domain = 'https://streamlabs.com/api/v1.0';
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
     * @param array|null $auth
     * @return StreamLabsService
     */
    public static function instance(array $auth = null) {
        /** @var StreamLabsService $instance */
        $instance = parent::instance();
        if(empty($auth)) {
            $auth = $instance->getDefaultAuth();
        }
        $instance->setAuth($auth);
        return $instance;
    }

    public function setAuth(array $auth) {
        $this->auth = $auth;
    }

    public function getDefaultAuth(){
        if(empty($this->default)) {
            try {
                $userService = UserService::instance();
                $this->default = $userService->getUserAuthProfile(Config::$a['streamlabs']['default_user'], 'streamlabs');
            } catch (\Exception $e) {
                $n = new Exception("Error getting default auth profile.", $e);
                Log::error($n);
            }
        }
        return $this->default;
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
        $token = $this->getAlwaysValidToken($auth);
        if (!empty($token)) {
            $client = new GuzzleHttp\Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
            return $client->post("$this->domain/alerts", [
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
        $token = $this->getAlwaysValidToken($auth);
        if (!empty($token)) {
            $client = new GuzzleHttp\Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
            return $client->post("$this->domain/donations", [
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
        $userService = UserService::instance();
        $client = new GuzzleHttp\Client(['timeout' => 15, 'connect_timeout' => 10]);
        $response = $client->post("$this->domain/token", [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => Config::$a['streamlabs']['client_id'],
                'client_secret' => Config::$a['streamlabs']['client_secret'],
                'redirect_uri'  => Config::$a['streamlabs']['redirect_uri'],
                'refresh_token' => $auth['refreshToken']
            ]
        ]);
        $data = json_decode((string) $response->getBody(), true);
        if(!empty($response) && $response->getStatusCode() == Http::STATUS_OK){
            $userService->updateUserAuthProfile($auth['userId'], "streamlabs", [
                'refreshToken'  => $data ['refresh_token'],
                'authCode'      => $data ['access_token'],
                'createdDate'   => Date::getDateTime('NOW')->format('Y-m-d H:i:s'),
                'modifiedDate'  => Date::getDateTime('NOW')->format('Y-m-d H:i:s')
            ]);
            $token = $data ['access_token'];
        }
        return $token;
    }

    /**
     * @param array $auth
     * @return string
     *
     * @throws DBALException
     */
    private function getAlwaysValidToken(array $auth){
        $createdDate = Date::getDateTime($auth['createdDate']);
        $token = $auth['authCode'];
        if ($createdDate->getTimestamp() + 3600 < Date::getDateTime()->getTimestamp()) {
            $token = $this->renewToken($auth);
        }
        return $token;
    }

    /**
     * @return string
     */
    public function getAuthenticationUrl() {
        $client = new OAuth2\Client ( Config::$a['streamlabs']['client_id'], Config::$a['streamlabs']['client_secret'] );
        return $client->getAuthenticationUrl ( "$this->domain/authorize", Config::$a['streamlabs']['redirect_uri'], [
            'scope' => 'alerts.create donations.create',
        ]);
    }

    /**
     * @param $code
     * @return array
     *
     * @throws Exception
     * @throws OAuth2\Exception
     * @throws OAuth2\InvalidArgumentException
     */
    public function authenticate($code) {
        $auth = null;
        $client = new OAuth2\Client ( Config::$a['streamlabs']['client_id'], Config::$a['streamlabs']['client_secret'] );
        $response = $client->getAccessToken ( "$this->domain/token", 'authorization_code', [
            'redirect_uri' => Config::$a['streamlabs']['redirect_uri'],
            'code' => $code
        ]);
        if (is_array($response) && isset($response['result']) && is_array($response['result']) && isset($response['result']['access_token']) && isset($response['result']['refresh_token'])){
            $auth = [
                'access_token'  => $response['result']['access_token'],
                'refresh_token' => $response['result']['refresh_token']
            ];
        } else {
            throw new Exception ( 'Bad response from streamlabs' );
        }
        return $auth;
    }

}