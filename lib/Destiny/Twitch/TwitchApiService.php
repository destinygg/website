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

    public static $STREAM_INFO = [
        'live'             => false,
        'game'             => '',
        'preview'          => null,
        'animated_preview' => null,
        'status_text'      => null,
        'started_at'       => null,
        'ended_at'         => null,
        'duration'         => 0,
        'viewers'          => 0,
        'host'             => null
    ];

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
    public static function checkForHostingChange(array $lasthosting, array $hosting){
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
     * @return array
     */
    public function getChannelHostWithInfo($id){
        $info = [];
        $host = $this->getChannelHost($id);
        if(!empty($host) && isset($host['target_login'])){
            $target = $this->getChannel($host['target_login']);
            if(!empty($target) && isset($target['display_name']) && isset($target['url'])){
                $info['id']           = $target['_id'];
                $info['url']          = $target['url'];
                $info['display_name'] = $target['display_name'];
            }
        }
        return $info;
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
     * @param int $limit
     * @return array
     */
    public function getPastBroadcasts($limit = 4) {
        $name = Config::$a['twitch']['user'];
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
     * @param $name
     * @return array|null
     */
    public function getStream($name) {
        $client = new Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get("$this->apiBase/streams/$name", [
            'headers' => [
                'User-Agent' => Config::userAgent(),
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id']
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            try {
                return \GuzzleHttp\json_decode($response->getBody(), true);
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
                'Client-ID' => Config::$a['oauth_providers']['twitch']['client_id']
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
     * @param string $name stream name
     * @return array
     */
    public function getStreamInfo($name) {
        $cache = Application::getNsCache();
        $streaminfo = self::$STREAM_INFO;
        $channel = $this->getChannel($name);
        if (!empty ( $channel )){
            $streaminfo['game'] = $channel ['game'];
            $streaminfo['status_text'] = $channel ['status'];
        }

        // Stream object is an object when streamer is ONLINE, otherwise null
        $stream = $this->getStream($name);
        $broadcasts = $this->getPastBroadcasts(1);
        if ((!empty($stream) && isset ($stream ['stream']) && !empty($stream ['stream'])) && !(isset ($stream ['status']) && $stream ['status'] == 503)) {
            $created = Date::getDateTime($stream ['stream']['created_at']);
            $streaminfo['live'] = true;
            $streaminfo['started_at'] = $created->format(Date::FORMAT);
            $streaminfo['duration'] = time() - $created->getTimestamp();
            $streaminfo['viewers'] = $stream['stream']['viewers'];
            $streaminfo['preview'] = $stream['stream']['preview']['medium'];
            $streaminfo['animated_preview'] = $streaminfo['preview'];
            $streaminfo['ended_at'] = Date::getDateTime()->format(Date::FORMAT);
            $cache->save('lasttimeonline', $streaminfo['ended_at']);
        } else if(!empty($broadcasts) && isset($broadcasts['videos']) && !empty($broadcasts['videos'])){
            $video = $broadcasts['videos'][0];
            $streaminfo['preview'] = $video['preview'];
            $streaminfo['animated_preview'] = isset($video['animated_preview']) ? $video['animated_preview'] : $streaminfo['preview'];
            $recorded_at = Date::getDateTime($video['recorded_at']);
            $streaminfo['ended_at'] = $cache->contains('lasttimeonline') ? $cache->fetch('lasttimeonline') : $recorded_at->format(Date::FORMAT);
        }

        return $streaminfo;
    }
}