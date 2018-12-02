<?php
namespace Destiny\Twitch;

use Destiny\Common\Application;
use Destiny\Common\Log;
use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use GuzzleHttp\Client;

/**
 * @method static TwitchApiService instance()
 */
class TwitchApiService extends Service {

    public static $HOST_UNCHANGED   = 0;
    public static $HOST_NOW_HOSTING = 1;
    public static $HOST_STOPPED     = 2;

    private $apiBase = 'https://api.twitch.tv/kraken';
    private $tmiBase = 'https://tmi.twitch.tv';

    /**
     * @param array $lasthosting
     * @param array $hosting
     * @return int
     *  0 no change
     *  1 now hosting
     *  2 stopped hosting
     */
    public static function checkForHostingChange(array $lasthosting, array $hosting = null){
        if ($hosting === null) {
            $hosting = [];
        }
        if (!isset($lasthosting['id']) && isset($hosting['id']))
            // now hosting
            $state = self::$HOST_NOW_HOSTING;
        else if((isset($lasthosting['id']) && isset($hosting['id'])) && $lasthosting['id'] != $hosting['id'])
            // now hosting different
            $state = self::$HOST_NOW_HOSTING;
        else if (isset($lasthosting['id']) && !isset($hosting['id']))
            // stopped hosting
            $state = self::$HOST_STOPPED;
        else
            // unchanged
            $state = self::$HOST_UNCHANGED;
        return $state;
    }

    /**
     * What channel {you} are hosting
     * @param $id
     * @return array|null
     */
    public function getChannelHostWithInfo($id) {
        $host = $this->getChannelHost($id);
        if (!empty($host) && isset($host['target_login'])) {
            $target = $this->getStreamLiveDetails($host['target_login']);
            if (!empty($target) && isset($target['channel'])) {
                $channel = $target['channel'];
                return [
                    'id' => $channel['_id'],
                    'url' => $channel['url'],
                    'name' => $channel['name'],
                    'preview' => $target['preview']['medium'],
                    'display_name' => $channel['display_name'],
                ];
            }
        }
        return null;
    }

    /**
     * What channel {you} are hosting
     * @param int $id stream id
     * @return array
     */
    public function getChannelHost($id){
        $client = new Client(['timeout' => 15, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("$this->tmiBase/hosts", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id']
            ],
            'query' => [
                'include_logins' => '1',
                'host' => $id
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $json = \GuzzleHttp\json_decode($response->getBody(), true);
                if (!empty($json) && isset($json['hosts']))
                    return $json['hosts'][0];
                return $json;
            } catch (\InvalidArgumentException $e) {
                Log::error("Failed to parse channel host. " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @param int $limit
     * @return array
     */
    public function getPastBroadcasts($name, $limit = 4) {
        $client = new Client(['timeout' => 15, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/channels/$name/videos", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id']
            ],
            'query' => [
                'broadcasts' => true,
                'limit' => $limit
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            try {
                return \GuzzleHttp\json_decode($response->getBody(), true);
            } catch (\InvalidArgumentException $e) {
                Log::error("Failed to parse past broadcasts." . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Stream object is an object when streamer is ONLINE, otherwise null
     * @param $name
     * @return array|null
     */
    public function getStreamLiveDetails($name) {
        $client = new Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/streams/$name", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id']
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            try {
                $data = \GuzzleHttp\json_decode($response->getBody(), true);
                if (isset($data['status']) && $data['status'] == 503) {
                    return null;
                }
                return $data['stream'];
            } catch (\InvalidArgumentException $e) {
                Log::error("Failed to parse streams. " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * @param string $name channel name
     * @return array|null
     */
    public function getChannel($name) {
        $client = new Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/channels/$name", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id'],
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            try {
                return \GuzzleHttp\json_decode($response->getBody(), true);
            } catch (\InvalidArgumentException $e) {
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
     *   'host'             => null
     * ]
     * @param string $name stream name
     * @param string|false $lastOnline
     * @return array
     */
    public function getStreamStatus($name, $lastOnline = false) {
        $channel = $this->getChannel($name);

        if (empty($channel)) {
            return null;
        }

        $live = $this->getStreamLiveDetails($name);
        // if there are live details
        //   use the current time
        // else if there are no live details
        //   if there is a cache lastOnline
        //     use lastOnline
        //   else
        //     use channel[updated_date]
        $lastSeen = (empty($live) ? (!empty($lastOnline) ? Date::getDateTime($lastOnline) : Date::getDateTime($channel['updated_at'])) : Date::getDateTime())->format(Date::FORMAT);
        $data = [
            'live' => !empty($live),
            'game' => $channel['game'],
            'status_text' => $channel['status'],
            'ended_at' => $lastSeen,
        ];

        if (!empty($live)) {

            $created = Date::getDateTime($live['created_at']);
            $data['host'] = null;
            $data['preview'] = $live['preview']['medium'];
            $data['started_at'] = $created->format(Date::FORMAT);
            $data['duration'] = time() - $created->getTimestamp();
            $data['viewers'] = $live['viewers'];

        } else {

            $broadcasts = $this->getPastBroadcasts($name, 1);
            $lastPreview = (!empty($broadcasts) && isset($broadcasts['videos']) && !empty($broadcasts['videos'])) ? $broadcasts['videos'][0]['preview'] : null;
            $data['host'] = $this->getChannelHostWithInfo($channel['_id']);
            $data['preview'] = !empty($data['host']) ? $data['host']['preview'] : $lastPreview;
            $data['started_at'] = null;
            $data['duration'] = 0;
            $data['viewers'] = 0;

        }

        return $data;
    }

}