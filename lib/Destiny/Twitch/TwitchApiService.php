<?php
namespace Destiny\Twitch;

use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;

/**
 * @method static TwitchApiService instance()
 */
class TwitchApiService extends Service {
    
    /**
     * @param array $options
     * @return CurlBrowser
     */
    public function getPastBroadcasts(array $options = array()) {
        return new CurlBrowser ( array_merge ( array (
            'timeout' => 25,
            'url' => 'https://api.twitch.tv/kraken/channels/'. Config::$a ['twitch'] ['user'] .'/videos?broadcasts=true&limit=' . 4,
            'contentType' => MimeType::JSON 
        ), $options ) );
    }

    /**
     * @param array $options
     * @return CurlBrowser
     */
    public function getStreamInfo(array $options = array()) {
        return new CurlBrowser ( array_merge ( array(
            'url' => 'https://api.twitch.tv/kraken/streams/'. Config::$a ['twitch'] ['user'] .'/',
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {

                $streaminfo = array(
                    'live' => false,
                    'created_at' => null,
                    'length' => -1,
                    'viewers' => -1,
                    'status' => null,
                    'game' => '',
                    'video_banner' => null,
                    'preview' => [
                        'small' => null,
                        'medium' => null,
                        'large' => null
                    ]
                );

                // If there is a stream object the user is online
                // else query just the channel info, which is always available
                if (!empty($json) && !(isset ($json ['status']) && $json ['status'] == 503)) {
                    if (isset ($json ['stream']) && !empty($json ['stream'])) {
                        $created = Date::getDateTime($json ['stream']['created_at']);
                        $streaminfo['live'] = true;
                        $streaminfo['viewers'] = $json['stream']['viewers'];
                        $streaminfo['preview']['small'] = $json['stream']['preview']['small'];
                        $streaminfo['preview']['medium'] = $json['stream']['preview']['medium'];
                        $streaminfo['preview']['large'] = $json['stream']['preview']['large'];
                        $streaminfo['created_at'] = $created->format(Date::FORMAT);
                        $streaminfo['length'] = time() - $created->getTimestamp();
                        $streaminfo['video_banner'] = $json['stream']['channel'] ['video_banner'];
                        $streaminfo['status'] = $json['stream']['channel'] ['status'];
                        $streaminfo['game'] = $json['stream']['channel'] ['game'];
                    } else {
                        $streaminfo['live'] = false;
                        $channel = TwitchApiService::instance ()->getChannel ()->getResponse ();
                        if (!empty ( $channel ) && !(isset ($json ['status']) && $json ['status'] == 503)){
                            $streaminfo['video_banner'] = $channel ['video_banner'];
                            $streaminfo['status'] = $channel ['status'];
                            $streaminfo['game'] = $channel ['game'];
                        }
                    }
                }

                return $streaminfo;
            }
        ), $options ) );
    }

    /**
     * @param array $options
     * @return CurlBrowser
     */
    public function getChannel(array $options = array()) {
        return new CurlBrowser ( array_merge ( array (
            'url' => 'https://api.twitch.tv/kraken/channels/' . Config::$a ['twitch'] ['user'],
            'contentType' => MimeType::JSON 
        ), $options ) );
    }
}