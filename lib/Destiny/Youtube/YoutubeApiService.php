<?php
namespace Destiny\Youtube;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\String;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;

/**
 * @method static YoutubeApiService instance()
 */
class YoutubeApiService extends Service {

    /**
     * Get a the latest playlist from google
     *
     * @param array $options
     * @param array $params
     * @throws Exception
     * @return CurlBrowser
     */
    public function getYoutubePlaylist(array $options = array(), array $params = array()) {
        // Get the channel ID's from a specific person
        // GET https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=StevenBonnell&key={YOUR_API_KEY}
        $params ['limit'] = (isset ( $params ['limit'] )) ? intval ( $params ['limit'] ) : 4;
        return new CurlBrowser ( array_merge ( array (
            'url' => new String ( 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={playlistId}&key={apikey}&maxResults={limit}', array (
                'playlistId' => Config::$a ['youtube'] ['playlistId'],
                'apikey' => Config::$a ['youtube'] ['apikey'],
                'limit' => $params ['limit'] 
            ) ),
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {
                if (is_array ( $json ['items'] )) {
                    foreach ( $json ['items'] as $i => $item ) {
                        $item ['snippet'] ['publishedAt'] = Date::getDateTime ( $item ['snippet'] ['publishedAt'] );
                    }
                } else {
                    throw new Exception ( 'Youtube API Down' );
                }
                return $json;
            } 
        ), $options ) );
    }

}