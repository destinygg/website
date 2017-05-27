<?php
namespace Destiny\Youtube;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;

/**
 * @method static YoutubeApiService instance()
 */
class YoutubeApiService extends Service {

    /**
     * @param array $options
     * @param array $params
     * @throws Exception
     * @return CurlBrowser
     */
    public function getYoutubePlaylist(array $options = array(), array $params = array()) {
        // Get the channel ID's from a specific person
        // GET https://www.googleapis.com/youtube/v3/channels?part=contentDetails&forUsername=Destiny&key={1}
        // GET https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&channelId={0}&key={1}
        $params ['limit'] = (isset ( $params ['limit'] )) ? intval ( $params ['limit'] ) : 4;
        $url = 'https://www.googleapis.com/youtube/v3/search?order=date&type=video&part=snippet&channelId='. Config::$a ['youtube'] ['playlistId'] .'&key='. Config::$a ['youtube'] ['apikey'] .'&maxResults=' . intval($params ['limit']);
        return new CurlBrowser ( array_merge ( array (
            'url' => $url,
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