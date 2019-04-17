<?php namespace Destiny\Twitch;

use Destiny\Chat\ChatRedisService;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Log;
use Destiny\Common\Request;
use Destiny\Common\Service;
use Destiny\Common\Utils\FilterParams;
use Destiny\Common\Utils\FilterParamsException;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;

/**
 * @method static TwitchWebHookService instance()
 * @see https://dev.twitch.tv/docs/api/webhooks-reference/
 */
class TwitchWebHookService extends Service {

    const API_BASE = 'https://api.twitch.tv/helix';
    const MODE_SUBSCRIBE = 'subscribe';
    const MODE_UNSUBSCRIBE = 'unsubscribe';
    const MODE_DENIED = 'denied';
    const GET_TOPIC_KEY = 'k';

    const TOPIC_STREAM = 'topic-stream-changed';
    const TOPIC_FOLLOW = 'topic-user-follows';
    const TOPIC_USER_CHANGED = 'topic-user-changed';
    const TOPIC_GAME_ANALYTICS = 'topic-game-analytics';
    const TOPIC_EXTENSION_ANALYTICS = 'topic-extension-analytics';

    const CACHE_KEY_PREFIX = "twitch.stream.";

    /**
     * @see https://dev.twitch.tv/docs/api/webhooks-reference/#subscribe-tounsubscribe-from-events
     * @param string $mode subscribe|unsubscribe
     * @param string $key appended to the end of the callback url
     * @param string $topic the full url for the topic
     * @param string $userId the twitch user id
     * @param int $ttl
     * @return bool
     * @throws Exception
     */
    public function sendSubscriptionRequest($mode, $key, $topic, $userId, $ttl = 86400) {
        $conf = Config::$a['twitch_webhooks'];
        $client = new Client(['timeout' => 15, 'connect_timeout' => 10, 'http_errors' => false]);
        $response = $client->post(self::API_BASE . '/webhooks/hub', [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => $conf['client_id']
            ],
            'form_params' => [
                'hub.mode' => $mode,
                'hub.callback' => $conf['callback'] . '?'. self::GET_TOPIC_KEY. '=' . urlencode($key) .'&user_id=' . urlencode($userId),
                'hub.topic' => $topic,
                'hub.lease_seconds' => $ttl,
                'hub.secret' => $conf['secret']
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_ACCEPTED) {
            return true;
        }
        throw new Exception('Error sending twitch webhook subscription request. ' . $response->getBody());
    }

    /**
     * @param Request $request
     *
     * @return bool
     * @throws TwitchWebHookException
     */
    private function validateIncomingCallback(Request $request) {
        $conf = Config::$a['twitch_webhooks'];
        // Returned as X-Hub-Signature sha256(secret, notification_bytes)
        $signature = $request->header('HTTP_X_HUB_SIGNATURE');
        if (empty($signature)) {
            throw new TwitchWebHookException('Empty signature');
        }
        if ($signature != 'sha256=' . hash_hmac('sha256', $request->getBody(), $conf['secret'])) {
            throw new TwitchWebHookException('Invalid signature ' . $signature);
        }
        // Make sure the callback get param was returned
        $topic = $request->param(self::GET_TOPIC_KEY);
        if (empty($topic)) {
            throw new TwitchWebHookException('Empty $topic');
        }
        return true;
    }

    /**
     * @param Request $request
     * @return string
     * @throws TwitchWebHookException
     */
    public function handleIncomingWebhook(Request $request) {
        $this->validateIncomingCallback($request);
        $topic = $request->param(self::GET_TOPIC_KEY);
        switch ($topic) {
            case self::TOPIC_STREAM:
                $this->handleStreamChangeWebhook($request);
                break;
            case self::TOPIC_FOLLOW:
                Log::debug("Unhandled $topic");
                break;
            case self::TOPIC_USER_CHANGED:
                Log::debug("Unhandled $topic");
                break;
            case self::TOPIC_GAME_ANALYTICS:
                Log::debug("Unhandled $topic");
                break;
            case self::TOPIC_EXTENSION_ANALYTICS:
                Log::debug("Unhandled $topic");
                break;
        }
        return 'ok';
    }

    /**
     * This is the incoming request for stream change event
     * TODO we currently do not store anything other than if the stream is online
     *
     * @param Request $request
     * @see https://dev.twitch.tv/docs/api/webhooks-reference/#example-notification-payload-for-other-stream-change-events
     */
    private function handleStreamChangeWebhook(Request $request) {
        $payload = json_decode($request->getBody(), true);
        Log::info('handling twitch stream change webhook', $payload);
        if (!empty($payload) && isset($payload['data']) && is_array($payload['data'])) {
            $userId = $request->param('user_id');
            if (!empty($userId)) {
                $cache = Application::getNsCache();
                $existing = $cache->fetch(self::CACHE_KEY_PREFIX . $userId) ?: ['live' => false];
                $waslive = $existing['live'] ?? false;
                $data = $payload['data'][0] ?? null;
                if (!empty($data)) {
                    // If the event data, and the user_id GET are not the same
                    if ($userId != $data['user_id']) {
                        Log::error('Invalid stream change payload.', $data[0]);
                        return;
                    }
                    if ($data['type'] === 'live') {
                        $cache->save(self::CACHE_KEY_PREFIX . $userId, ['time' => time(), 'live' => true]);
                        if (!$waslive && $userId == Config::$a['twitch']['user']) {
                            ChatRedisService::instance()->sendBroadcast("Destiny is now live :) " . $data['title']);
                        }
                        return;
                    }
                } else {
                    if ($waslive && $userId == Config::$a['twitch']['user']) {
                        ChatRedisService::instance()->sendBroadcast("Destiny is now offline :( ");
                    }
                }
                // OFFLINE
                $cache->save(self::CACHE_KEY_PREFIX . $userId, ['time' => time(), 'live' => false]);
            } else {
                Log::warn('Missing user id for twitch change webhook');
            }
        } else {
            Log::warn('Missing payload for twitch change webhook');
        }
    }

    /**
     * This is the incoming request after the subscribe request is sent
     * Always return the challenge on success
     *
     * @param Request $request
     * @return string
     * @throws FilterParamsException
     */
    public function handleIncomingNotify(Request $request) {
        $gets = $request->get();
        FilterParams::required($gets, 'hub_topic');
        FilterParams::required($gets, 'hub_mode');
        if ($gets['hub_mode'] == self::MODE_DENIED) {
            Log::error('Denied twitch webhook subscription.', ['reason' => $get['hub_reason'] ?? 'Unspecified']);
            return 'denied';
        }
        Log::info('Handled incoming notification.', ['topic' => $gets['hub_topic']]);
        return $gets['hub_challenge'] ?? 'none';
    }


}

class TwitchWebHookException extends Exception {}