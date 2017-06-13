<?php
namespace Destiny\StreamLabs;

use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\User\UserService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use OAuth2;
use GuzzleHttp;

/**
 * @method static StreamLabsService instance()
 */
class StreamLabsService extends Service {

    /**
     * @var string
     */
    public $authProvider = 'twitchalerts';
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

    public function setAuth(array $auth) {
        $this->auth = $auth;
    }

    public function useDefaultAuth(){
        if(empty($this->default)) {
            $this->default = UserService::instance()->getUserAuthProfile(12, 'twitchalerts');
        }
        $this->auth = $this->default;
    }

    /**
     * @param array $auth
     * @param array $args
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function sendAlert(array $args, array $auth = null){
        if($auth === null) {
            $auth = $this->auth;
        }
        $token = $this->getAlwaysValidToken($auth);
        if (!empty($token)) {
            $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
            return $client->post("$this->domain/alerts", [
                'form_params' => array_merge($args, ['access_token'=> $token])]
            );
        }
        return null;
    }

    /**
     * @param array $args
     * @param array $auth
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function sendDonation(array $args, array $auth = null){
        if($auth === null) {
            $auth = $this->auth;
        }
        $token = $this->getAlwaysValidToken($auth);
        if (!empty($token)) {
            $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
            return $client->post("$this->domain/donations", [
                'form_params' => array_merge($args, ['access_token'=> $token])
            ]);
        }
        return null;
    }

    /**
     * @param array $auth
     * @return string
     *
     * @throws \Exception
     */
    private function renewToken(array $auth){
        $token = $auth['authCode'];
        $userService = UserService::instance();
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5]);
        $response = $client->post("$this->domain/token", [
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'client_id'     => Config::$a['twitchalerts']['client_id'],
                'client_secret' => Config::$a['twitchalerts']['client_secret'],
                'redirect_uri'  => Config::$a['twitchalerts']['redirect_uri'],
                'refresh_token' => $auth['refreshToken']
            ]
        ]);
        $data = json_decode((string) $response->getBody(), true);
        if(!empty($response) && $response->getStatusCode() == Http::STATUS_OK){
            $userService->updateUserAuthProfile($auth['userId'], "twitchalerts", [
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
     * @throws \Exception
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
     *
     * @throws Exception
     */
    public function getAuthenticationUrl() {
        $client = new OAuth2\Client ( Config::$a['twitchalerts']['client_id'], Config::$a['twitchalerts']['client_secret'] );
        return $client->getAuthenticationUrl ( "$this->domain/authorize", Config::$a['twitchalerts']['redirect_uri'], [
            'scope' => 'alerts.create donations.create',
        ]);
    }

    /**
     * @param $code
     * @return array
     *
     * @throws \Exception
     */
    public function authenticate($code) {
        $auth = null;
        $client = new OAuth2\Client ( Config::$a['twitchalerts']['client_id'], Config::$a['twitchalerts']['client_secret'] );
        $response = $client->getAccessToken ( "$this->domain/token", 'authorization_code', [
            'redirect_uri' => Config::$a['twitchalerts']['redirect_uri'],
            'code' => $code
        ]);
        if (is_array($response) && isset($response['result']) && is_array($response['result']) && isset($response['result']['access_token']) && isset($response['result']['refresh_token'])){
            $auth = [
                'access_token'  => $response['result']['access_token'],
                'refresh_token' => $response['result']['refresh_token']
            ];
        } else {
            throw new Exception ( 'Bad response from twitchalerts' );
        }
        return $auth;
    }

}