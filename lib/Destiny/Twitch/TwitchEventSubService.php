<?php
namespace Destiny\Twitch;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Service;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Http;
use Destiny\Twitch\TwitchAuthHandler;
use GuzzleHttp\Exception\RequestException;

class TwitchEventSubService extends Service {
    const API_BASE = 'https://api.twitch.tv/helix';

    const EVENT_STREAM_ONLINE = 'stream.online';
    const EVENT_STREAM_OFFLINE = 'stream.offline';

    public function subscribe(string $subscriptionType, int $userId) {
        $config = Config::$a['oauth_providers']['twitch'];
        $callback = Config::$a['twitch']['webhooks_callback'];
        $secret = Config::$a['twitch']['eventsub_secret'];

        $client = HttpClient::instance();
        try {
            $response = $client->post(self::API_BASE . '/eventsub/subscriptions', [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Client-ID' => $config['client_id'],
                    'Authorization' => 'Bearer ' . $this->getAppAccessToken()
                ],
                'json' => [
                    'type' => $subscriptionType,
                    'version' => '1',
                    'condition' => [
                        'broadcaster_user_id' => strval($userId)
                    ],
                    'transport' => [
                        'method' => 'webhook',
                        'callback' => $callback,
                        'secret' => $secret
                    ]
                ],
                'http_errors' => true
            ]);

            return true;
        } catch (RequestException $e) {
            throw new Exception('Error subscribing to Twitch EventSub event.', $e);
        }
    }

    public function handleCallbackVerificationRequest(Request $request): string {
        $headers = json_encode($request->headers);
        Log::debug("Headers in EventSub callback verification request are `$headers`.");

        if (!$this->verifyMessageSignature($request)) {
            throw new TwitchEventSubSignatureInvalidException('Twitch EventSub callback signature is invalid.');
        }

        $payload = json_decode($request->getBody(), true);
        $challenge = $payload['challenge'];
        if (empty($challenge)) {
            throw new Exception('Challenge is missing from verification request.');
        }

        return $challenge;
    }

    // https://dev.twitch.tv/docs/eventsub#verify-a-signature
    private function verifyMessageSignature(Request $request): bool {
        $messageId = $request->header('HTTP_TWITCH_EVENTSUB_MESSAGE_ID');
        $messageTimestamp = $request->header('HTTP_TWITCH_EVENTSUB_MESSAGE_TIMESTAMP');
        $requestBody = $request->getBody();

        if (empty($messageId) || empty($messageTimestamp)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Required headers are empty.');
        }
        if (empty($requestBody)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Request body is empty.');
        }

        Log::debug("Message ID is `$messageId`.");
        Log::debug("Message timestamp is `$messageTimestamp`.");
        Log::debug("Request body is `$requestBody`.");

        $signature = hash_hmac(
            'sha256',
            $messageId . $messageTimestamp . $requestBody,
            Config::$a['twitch']['eventsub_secret']
        );

        $requestSignature = $request->header('HTTP_TWITCH_EVENTSUB_MESSAGE_SIGNATURE');
        if (empty($requestSignature)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Request signature is missing.');
        }

        Log::debug("Request header signature is `$requestSignature`. Computed signature is `sha256=$signature`.");

        return $requestSignature === "sha256=$signature";
    }

    public function handleIncomingEvent(Request $request) {
        if (!$this->verifyMessageSignature($request)) {
            throw new TwitchEventSubSignatureInvalidException('Twitch EventSub callback signature is invalid.');
        }

        $payload = json_decode($request->getBody());
        $type = $payload->subscription->type ?? null;

        switch ($type) {
            case self::EVENT_STREAM_ONLINE:
                $this->handleStreamStatusChange(true);
                break;
            case self::EVENT_STREAM_OFFLINE:
                $this->handleStreamStatusChange(false);
                break;
            default:
                break;
        }
    }

    private function handleStreamStatusChange(bool $online) {
        $redis = ChatRedisService::instance();
        $redis->sendBroadcast(
            $online ?
            Config::$a['twitch']['online_message'] :
            Config::$a['twitch']['offline_message']
        );
    }

    private function getSubscriptions(): array {
        $config = Config::$a['oauth_providers']['twitch'];

        $client = HttpClient::instance();
        try {
            $response = $client->get(self::API_BASE . '/eventsub/subscriptions', [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Client-ID' => $config['client_id'],
                    'Authorization' => 'Bearer ' . $this->getAppAccessToken()
                ],
                'http_errors' => true
            ]);

            $payload = json_decode($response->getBody());
            $subbedEvents = $payload->data;

            return $subbedEvents;
        } catch (RequestException $e) {
            throw new Exception('Error getting Twitch EventSub subscriptions.', $e);
        }
    }

    public function getActiveSubscriptionEventTypes(): array {
        $subbedEvents = $this->getSubscriptions();

        // We only receive notifications for event types marked as
        // `enabled`.
        $subbedEvents = array_filter(
            $subbedEvents,
            function($subbedEvent) {
                return $subbedEvent->status === 'enabled';
            }
        );

        $subbedEventTypes = array_map(
            function($subbedEvent) {
                return $subbedEvent->type;
            },
            $subbedEvents
        );

        // An array of all event types we're currently subscribed to.
        return $subbedEventTypes;
    }

    public function pruneInactiveSubscriptions() {
        $subbedEvents = $this->getSubscriptions();

        $subbedEvents = array_filter(
            $subbedEvents,
            function($subbedEvent) {
                return $subbedEvent->status !== 'enabled';
            }
        );

        $inactiveSubscriptionIds = array_map(
            function($subbedEvent) {
                return $subbedEvent->id;
            },
            $subbedEvents
        );

        $client = HttpClient::instance();
        $config = Config::$a['oauth_providers']['twitch'];
        try {
            foreach ($inactiveSubscriptionIds as $id) {
                Log::debug("Deleting subscription with ID `$id`.");
                $response = $client->delete(self::API_BASE . '/eventsub/subscriptions', [
                    'headers' => [
                        'User-Agent' => Config::userAgent(),
                        'Client-ID' => $config['client_id'],
                        'Authorization' => 'Bearer ' . $this->getAppAccessToken()
                    ],
                    'query' => [
                        'id' => $id
                    ],
                    'http_errors' => true
                ]);
            }
        } catch (RequestException $e) {
            throw new Exception("Failed to delete EventSub subscription with ID `$id`.", $e);
        }
    }

    public function isCallbackVerificationRequest(Request $request) {
        $messageType = $request->header('HTTP_TWITCH_EVENTSUB_MESSAGE_TYPE');
        Log::debug("EventSub message type is `$messageType`.");
        return !empty($messageType) && $messageType === 'webhook_callback_verification';
    }

    public function isNotificationRequest(Request $request) {
        $messageType = $request->header('HTTP_TWITCH_EVENTSUB_MESSAGE_TYPE');
        Log::debug("EventSub message type is `$messageType`.");
        return !empty($messageType) && $messageType === 'notification';
    }

    /**
     * Returns an app access token. If not in cache or expired, gets a new one and caches it.
     */
    private function getAppAccessToken(): string {
        $cache = Application::getNsCache();
        $twitchAuthHandler = TwitchAuthHandler::instance();

        $accessToken = $cache->fetch(TwitchApiService::CACHE_KEY_ACCESS_TOKEN);
        if (!$accessToken || !($twitchAuthHandler->validateToken($accessToken))) {
            Log::debug('App access token not in cache or expired. Getting a new one.');
            $response = $twitchAuthHandler->getToken(['grant_type' => TwitchAuthHandler::GRANT_TYPE_APP]);
            $accessToken = $response['access_token'];
            $cache->save(TwitchApiService::CACHE_KEY_ACCESS_TOKEN, $accessToken);
        }

        return $accessToken;
    }
}

class TwitchEventSubSignatureInvalidException extends Exception {}
