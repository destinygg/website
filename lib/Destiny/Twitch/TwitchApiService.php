<?php
namespace Destiny\Twitch;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;

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
        $json = (new CurlBrowser (array_merge(array(
            'timeout' => 25,
            'url' => 'https://tmi.twitch.tv/hosts?include_logins=1&host=' . $id,
            'headers' => ['Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']],
            'contentType' => MimeType::JSON
        ))))->getResponse();
        if(empty($json))
            $json = [];
        if(isset($json['hosts']))
            $json = $json['hosts'][0];
        return $json;
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getPastBroadcasts($limit=4) {
        return (new CurlBrowser (array_merge(array(
            'timeout' => 10,
            'url' => 'https://api.twitch.tv/kraken/channels/' . Config::$a ['twitch'] ['user'] . '/videos?broadcasts=true&limit=' . $limit,
            'headers' => ['Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']],
            'contentType' => MimeType::JSON
        ))))->getResponse();
    }

    /**
     * @return array
     */
    public function getStream() {
        return (new CurlBrowser (array_merge(array(
            'timeout' => 10,
            'url' => 'https://api.twitch.tv/kraken/streams/' . Config::$a ['twitch'] ['user'],
            'headers' => ['Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']],
            'contentType' => MimeType::JSON
        ))))->getResponse();
    }

    /**
     * @param $name string channel name
     * @return array
     */
    public function getChannel($name) {
        return (new CurlBrowser (array_merge(array(
            'timeout' => 10,
            'url' => 'https://api.twitch.tv/kraken/channels/' . $name,
            'headers' => ['Client-ID' => Config::$a['oauth']['providers']['twitch']['clientId']],
            'contentType' => MimeType::JSON
        ))))->getResponse();
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
            $streaminfo['animated_preview'] = $video['animated_preview'];
            $recorded_at = Date::getDateTime($video['recorded_at']);
            $streaminfo['ended_at'] = $cache->contains('lasttimeonline') ? $cache->fetch('lasttimeonline') : $recorded_at->format(Date::FORMAT);
        }

        return $streaminfo;
    }
}