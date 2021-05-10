<?php
namespace Destiny\Twitch;

use Destiny\Common\Config;
use Destiny\Common\HttpClient;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use InvalidArgumentException;

/**
 * @method static TwitchApiService instance()
 */
class TwitchApiService extends Service {

    const PRIVATE_API_URL = 'https://gql.twitch.tv/gql';
    const PRIVATE_API_CLIENT_ID = 'kimne78kx3ncx6brgo4mv6wki5h1ko';

    private $apiBase = 'https://api.twitch.tv/kraken';

    public function getApiCredentials(): array {
        return Config::$a['oauth_providers']['twitch'];
    }

    public function getHostedChannelForUser(int $userId): ?array {
        try {
            $httpClient = HttpClient::instance();
            $response = $httpClient->post(self::PRIVATE_API_URL, [
                'headers' => [
                    'User-Agent' => Config::userAgent(),
                    'Client-ID' => self::PRIVATE_API_CLIENT_ID,
                ],
                'json' => [
                    'query' => "query {
                      user(id: $userId) {
                        hosting {
                          id
                          login
                          displayName
                          stream {
                            previewImageURL(width: 320, height: 180)
                          }
                        }
                      }
                    }"
                ],
                'http_errors' => true,
            ]);

            $json = json_decode($response->getBody(), true);
            if (!empty($json) && isset($json['data']) && isset($json['data']['user']) && isset($json['data']['user']['hosting'])) {
                $hosting = $json['data']['user']['hosting'];
                return [
                    'id' => $hosting['id'],
                    'name' => $hosting['login'],
                    'display_name' => $hosting['displayName'],
                    'preview' => $hosting['stream']['previewImageURL'],
                    'url' => "https://twitch.tv/{$hosting['login']}",
                ];
            }
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            Log::error('Non-200 status code when getting hosted channel. ' . \GuzzleHttp\Psr7\str($e->getResponse()));
        } catch (Exception $e) {
            Log::error('Error getting hosted channel. ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @return array|mixed
     */
    public function getPastBroadcasts(int $channelId, int $limit = 4) {
        $conf = $this->getApiCredentials();
        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/channels/$channelId/videos", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Accept' => 'application/vnd.twitchtv.v5+json',
                'Client-ID' => $conf['client_id']
            ],
            'query' => [
                'broadcasts' => true,
                'limit' => $limit
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                return \GuzzleHttp\json_decode($response->getBody(), true);
            } catch (InvalidArgumentException $e) {
                Log::error("Failed to parse past broadcasts." . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Stream object is an object when streamer is ONLINE, otherwise null
     * @return array|mixed
     */
    public function getStreamLiveDetails(int $channelId) {
        $conf = $this->getApiCredentials();
        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/streams/$channelId", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Accept' => 'application/vnd.twitchtv.v5+json',
                'Client-ID' => $conf['client_id']
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $data = \GuzzleHttp\json_decode($response->getBody(), true);
                if (isset($data['status']) && $data['status'] == 503) {
                    return null;
                }
                return $data['stream'];
            } catch (InvalidArgumentException $e) {
                Log::error("Failed to parse streams. " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * @return array|null
     */
    public function getChannel(int $channelId) {
        $conf = $this->getApiCredentials();
        $client = HttpClient::instance();
        $response = $client->get("$this->apiBase/channels/$channelId", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Accept' => 'application/vnd.twitchtv.v5+json',
                'Client-ID' => $conf['client_id'],
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            try {
                return \GuzzleHttp\json_decode($response->getBody(), true);
            } catch (InvalidArgumentException $e) {
                Log::error("Failed to parse channel. " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * [
     *   'live'             => false,
     *   'game'             => '',
     *   'preview'          => null,
     *   'status_text'      => null,
     *   'started_at'       => null,
     *   'ended_at'         => null,
     *   'duration'         => 0,
     *   'viewers'          => 0,
     * ]
     * @return array|null
     */
    public function getStreamStatus(int $channelId, bool $lastOnline = false) {
        $channel = $this->getChannel($channelId);

        if (empty($channel)) {
            return null;
        }

        $live = $this->getStreamLiveDetails($channelId);
        // if there are live details
        //   use the current time
        // else if there are no live details
        //   if there is a cache lastOnline
        //     use lastOnline
        //   else
        //     use channel[updated_date]
        $lastSeen = (empty($live) ? (!empty($lastOnline) ? Date::getDateTime($lastOnline) : Date::getDateTime($channel['updated_at'])) : Date::getDateTime());

        $data = [
            'live' => !empty($live),
            'game' => $channel['game'],
            'status_text' => $channel['status'],
            'ended_at' => $lastSeen->format(Date::FORMAT),
        ];

        if (!empty($live)) {

            $created = Date::getDateTime($live['created_at']);
            $data['preview'] = $live['preview']['medium'];
            $data['started_at'] = $created->format(Date::FORMAT);
            $data['duration'] = time() - $created->getTimestamp();
            $data['viewers'] = $live['viewers'];

        } else {

            $broadcasts = $this->getPastBroadcasts($channelId, 1);
            $lastPreview = (!empty($broadcasts) && isset($broadcasts['videos']) && !empty($broadcasts['videos'])) ? $broadcasts['videos'][0]['preview'] : null;
            $data['preview'] = $lastPreview;
            $data['started_at'] = null;
            $data['duration'] = 0;
            $data['viewers'] = 0;

        }

        return $data;
    }

}
