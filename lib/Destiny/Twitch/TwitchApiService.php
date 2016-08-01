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
        'hash'             => null
    ];

    /**
     * @param int $limit
     * @return CurlBrowser
     */
    public function getPastBroadcasts($limit=4) {
        return new CurlBrowser (array_merge(array(
            'timeout' => 25,
            'url' => 'https://api.twitch.tv/kraken/channels/' . Config::$a ['twitch'] ['user'] . '/videos?broadcasts=true&limit=' . $limit,
            'contentType' => MimeType::JSON
        )));
    }

    /**
     * @return CurlBrowser
     */
    public function getStream() {
        return new CurlBrowser (array_merge(array(
            'timeout' => 25,
            'url' => 'https://api.twitch.tv/kraken/streams/' . Config::$a ['twitch'] ['user'],
            'contentType' => MimeType::JSON
        )));
    }

    /**
     * @return CurlBrowser
     */
    public function getChannel() {
        return new CurlBrowser (array_merge(array(
            'url' => 'https://api.twitch.tv/kraken/channels/' . Config::$a ['twitch'] ['user'],
            'contentType' => MimeType::JSON
        )));
    }

    /**
     * @return array
     */
    public function getStreamInfo() {
        $cache = Application::instance()->getCacheDriver();
        $streaminfo = self::$STREAM_INFO;

        $channel = $this->getChannel ()->getResponse ();
        if (!empty ( $channel )){
            $streaminfo['game'] = $channel ['game'];
            $streaminfo['status_text'] = $channel ['status'];
        }

        // Stream object is an object when streamer is ONLINE, otherwise null
        $stream = $this->getStream()->getResponse();
        $broadcasts = $this->getPastBroadcasts(1)->getResponse ();
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