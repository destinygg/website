<?php
namespace Destiny\StreamElements;

use Destiny\Common\Authentication\AbstractAuthService;
use Destiny\Common\Authentication\AuthProvider;
use Destiny\Common\HttpClient;
use Destiny\StreamElements\StreamElementsAuthHandler;
use Psr\Http\Message\ResponseInterface;

/**
 * @method static StreamElementsService instance()
 */
class StreamElementsService extends AbstractAuthService {

    public $apiBase = 'https://api.streamelements.com/kappa/v2';
    public $authBase = 'https://api.streamelements.com/oauth2/validate';
    public $provider = AuthProvider::STREAMELEMENTS;

    function afterConstruct() {
        parent::afterConstruct();
        $this->authHandler = StreamElementsAuthHandler::instance();
    }

    /**
     * @return ResponseInterface|null
     */
    public function sendAlert() {
        $token = $this->getValidAccessToken();
        if (!empty($token)) {
            // TODO implement
            return HttpClient::instance()->get($this->authBase, [
                'headers' => [
                    'Authorization' => "OAuth $token"
                ]
            ]);
        }
        return null;
    }

}