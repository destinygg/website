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
}