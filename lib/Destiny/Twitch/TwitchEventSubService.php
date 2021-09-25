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

class TwitchEventSubService extends Service {
    const API_BASE = 'https://api.twitch.tv/helix';

    const EVENT_STREAM_ONLINE = 'stream.online';
    const EVENT_STREAM_OFFLINE = 'stream.offline';

    const CACHE_KEY_ACCESS_TOKEN = 'accesstoken';

    public function subscribe(string $subscriptionType, int $userId) {
        $config = Config::$a['oauth_providers']['twitch'];
        $callback = Config::$a['twitch']['webhooks_callback'];
        $secret = Config::$a['twitch']['eventsub_secret'];

        $client = HttpClient::instance();
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
                    'secret' => strval($secret)
                ]
            ]
        ]);

        if ($response->getStatusCode() == Http::STATUS_ACCEPTED) {
            return true;
        } else {
            throw new Exception('Error sending Twitch EventSub subscription request. ' . $response->getBody());
        }
    }

    private function handleCallbackVerificationRequest(Request $request): string {
        if (!$this->verifyMessageSignature($request)) {
            throw new Exception('Twitch EventSub callback signature is invalid.');
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
        // Header values are returned as an array. If that header doesn't exist,
        // an empty array is returned.
        $messageId = $request->getHeader('Twitch-Eventsub-Message-Id');
        $messageTimestamp = $request->getHeader('Twitch-Eventsub-Message-Timestamp');
        $requestBody = $request->getBody();

        if (empty($messageId) || empty($messageTimestamp)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Required headers are empty.');
        }
        if (empty($requestBody)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Request body is empty.');
        }

        $signature = hash_hmac(
            'sha256',
            $messageId[0] + $messageTimestamp[0] + $requestBody,
            Config::$a['twitch']['eventsub_secret']
        );

        $requestSignature = $request->getHeader('Twitch-Eventsub-Message-Signature');
        if (empty($requestSignature)) {
            throw new Exception('Error verifying Twitch EventSub message signature. Request signature is missing.');
        }

        return $requestSignature[0] === "sha256=$signature";
    }

    private function handleIncomingEvent(Request $request) {
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
        if ($online) {
            $redis->sendBroadcast('Destiny is online!');
        } else {
            $redis->sendBroadcast('Destiny is offline...');
        }
    }

    /**
     * Returns an app access token. If not in cache or expired, gets a new one and caches it.
     */
    private function getAppAccessToken(): string {
        $cache = Application::getNsCache();
        $twitchAuthHandler = TwitchAuthHandler::instance();

        $accessToken = $cache->fetch(self::CACHE_KEY_ACCESS_TOKEN);
        if (!$accessToken || !($twitchAuthHandler->validateToken($accessToken))) {
            Log::debug('App access token not in cache or expired. Getting a new one.');
            $response = $twitchAuthHandler->getToken(['grant_type' => TwitchAuthHandler::GRANT_TYPE_APP]);
            $accessToken = $response['access_token'];
            $cache->save(self::CACHE_KEY_ACCESS_TOKEN, $accessToken);
        }

        return $accessToken;
    }
}
