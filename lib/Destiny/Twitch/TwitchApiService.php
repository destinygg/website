<?php
namespace Destiny\Twitch;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use GuzzleHttp;

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
     * @param $id int stream id
     * @return array
     */
    public function getChannelHost($id){
        $client = new GuzzleHttp\Client(['timeout' => 15, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('https://tmi.twitch.tv/hosts', [
            'headers' => [
                'Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId'],
                'User-Agent' => Config::userAgent()
            ],
            'query' => [
                'include_logins' => '1',
                'host' => $id
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            $json = GuzzleHttp\json_decode($response->getBody(), true);
            if(!empty($json) && isset($json['hosts']))
                return $json['hosts'][0];
            return $json;
        }
        return null;
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getPastBroadcasts($limit = 4) {
        $client = new GuzzleHttp\Client(['timeout' => 15, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('https://api.twitch.tv/kraken/channels/' . Config::$a ['twitch'] ['user'] . '/videos', [
            'headers' => [
                'Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId'],
                'User-Agent' => Config::userAgent()
            ],
            'query' => [
                'broadcasts' => true,
                'limit' => $limit
            ]
        ]);
        if ($response->getStatusCode() == Http::STATUS_OK) {
            return GuzzleHttp\json_decode($response->getBody(), true);
        }
        return null;
    }

    /**
     * @return null|array
     */
    public function getStream() {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('https://api.twitch.tv/kraken/streams/' . Config::$a ['twitch'] ['user'], [
            'headers' => [
                'Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId'],
                'User-Agent' => Config::userAgent()
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            return GuzzleHttp\json_decode($response->getBody(), true);
        }
        return null;
    }

    /**
     * @param $name string channel name
     * @return null|array
     */
    public function getChannel($name) {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('https://api.twitch.tv/kraken/channels/' . $name, [
            'headers' => [
                'Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId'],
                'User-Agent' => Config::userAgent()
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            return GuzzleHttp\json_decode($response->getBody(), true);
        }
        return null;
    }

    /**
     * @param $name string stream name
     * @return array
     */
    public function getStreamInfo($name) {
        $cache = Application::instance()->getCacheDriver();
        $streaminfo = self::$STREAM_INFO;

        $channel = $this->getChannel($name);
        if (!empty ( $channel )){
            $streaminfo['game'] = $channel ['game'];
            $streaminfo['status_text'] = $channel ['status'];
        }

        // Stream object is an object when streamer is ONLINE, otherwise null
        $stream = $this->getStream();
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