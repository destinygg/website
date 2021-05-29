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

    public function sendSubAlert(array $subscriptionType, string $message, string $username) {
        // We set `user_message` to a space rather than leaving it empty. If we
        // leave it empty, StreamLabs uses the value of the `message` param for
        // both, which results in the TTS reading the event metadata.
        $this->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
            'user_message' => $message ?: ' ',
            'message' => $this->buildEventMetadata(
                SubAlertEvent::SUB,
                [
                    'user' => $username,
                    'tier' => $subscriptionType['tier'],
                    'tierLabel' => $subscriptionType['tierLabel']
                ]
            )
        ]);
    }

    public function sendResubAlert(array $subscriptionType, string $message, string $username, int $streak) {
        $this->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
            'user_message' => $message ?: ' ',
            'message' => $this->buildEventMetadata(
                SubAlertEvent::RESUB,
                [
                    'user' => $username,
                    'tier' => $subscriptionType['tier'],
                    'tierLabel' => $subscriptionType['tierLabel'],
                    'streak' => $streak
                ]
            )
        ]);
    }

    public function sendDirectGiftAlert(array $subscriptionType, string $message, string $username, string $giftee) {
        $this->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
            'user_message' => $message ?: ' ',
            'message' => $this->buildEventMetadata(
                SubAlertEvent::DIRECT_GIFT,
                [
                    'user' => $username,
                    'tier' => $subscriptionType['tier'],
                    'tierLabel' => $subscriptionType['tierLabel'],
                    'giftee' => $giftee
                ]
            )
        ]);
    }

    public function sendMassGiftAlert(array $subscriptionType, string $message, string $username, int $quantity) {
        $this->sendAlert([
            'type' => StreamLabsAlertsType::ALERT_SUBSCRIPTION,
            'user_message' => $message ?: ' ',
            'message' => $this->buildEventMetadata(
                SubAlertEvent::MASS_GIFT,
                [
                    'user' => $username,
                    'tier' => $subscriptionType['tier'],
                    'tierLabel' => $subscriptionType['tierLabel'],
                    'quantity' => $quantity
                ]
            )
        ]);
    }

    /**
     * Encodes additional alert data for use in StreamLabs' `/alerts` endpoint.
     * Passed in via the `user_message` parameter.
     */
    private function buildEventMetadata(string $event, array $data) {
        return json_encode([
            'source' => 'dgg',
            'event' => $event,
            'data' => $data
        ]);
    }
}
