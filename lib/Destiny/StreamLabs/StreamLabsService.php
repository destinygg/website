<?php
namespace Destiny\StreamLabs;

/*
[
    'access_token' => '',
    'type' => '',
    'message' => '',
    'image_href' => '',
    'sound_href' => '',
    'special_text_color' => ''
];
[
    'access_token' => '',
    'name' => '',
    'message' => '',
    'identifier' => '',
    'amount' => '',
    'currency' => ''
];
 */

use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Psr\Http\Message\ResponseInterface;

/**
 * @method static StreamLabsService instance()
 */
class StreamLabsService extends AbstractAuthService {

    private $apiBase = 'https://streamlabs.com/api/v1.0';
    public $provider = AuthProvider::STREAMLABS;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = StreamLabsAuthHandler::instance();
    }

    /**
     * @return ResponseInterface|null
     */
    public function sendAlert(array $args) {
        $token = $this->getValidAccessToken();
        if (!empty($token)) {
            return HttpClient::instance()->post("$this->apiBase/alerts", [
                'headers' => ['User-Agent' => Config::userAgent()],
                'form_params' => array_merge($args, ['access_token'=> $token])]
            );
        }
        return null;
    }

    /**
     * @return ResponseInterface|null
     */
    public function sendDonation(array $args){
        $token = $this->getValidAccessToken();
        if (!empty($token)) {
            return HttpClient::instance()->post("$this->apiBase/donations", [
                'headers' => ['User-Agent' => Config::userAgent()],
                'form_params' => array_merge($args, ['access_token'=> $token])
            ]);
        }
        return null;
    }

}