<?php 
namespace Destiny\LastFm;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;

/**
 * @method static LastFMApiService instance()
 */
class LastFMApiService extends Service {
    
    /**
     * @return CurlBrowser
     */
    public function getLastPlayedTracks() {
        return new CurlBrowser ([
            'url' => 'http://ws.audioscrobbler.com/2.0/?api_key='. Config::$a ['lastfm']['apikey'] .'&user='. Config::$a ['lastfm']['user'] .'&method=user.getrecenttracks&limit=3&format=json',
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {
                return $this->parseFeedResponse('recenttracks', $json);
            }
        ]);
    }

    /**
     * @return CurlBrowser
     */
    public function getTopTracks() {
        return new CurlBrowser ([
            'url' => 'http://ws.audioscrobbler.com/2.0/?api_key='. Config::$a ['lastfm']['apikey'] .'&user='. Config::$a ['lastfm']['user'] .'&method=user.gettoptracks&limit=3&format=json',
            'contentType' => MimeType::JSON,
            'onfetch' => function ($json) {
                return $this->parseFeedResponse('toptracks', $json);
            }
        ]);
    }

    private function parseFeedResponse($rootNode, array $json) {
        if (! $json || isset ( $json ['error'] ) && $json ['error'] > 0 || count ( $json [$rootNode] ['track'] ) <= 0) {
            throw new Exception ( 'Error fetching tracks' );
        }
        foreach ( $json [$rootNode] ['track'] as $i => $track ) {
            // Timezone DST = -1
            if (! isset ( $track ['@attr'] ) || (!isset($track ['@attr'] ['nowplaying']) ||$track ['@attr'] ['nowplaying'] != true)) {
                if (! empty ( $track ['date'] )) {
                    $json [$rootNode] ['track'] [$i] ['date'] ['uts]'] = $track ['date'] ['uts'];
                    $json [$rootNode] ['track'] [$i] ['date_str'] = Date::getDateTime ( $track ['date'] ['uts'] )->format ( Date::FORMAT );
                }
            } else {
                $json [$rootNode] ['track'] [$i] ['date_str'] = '';
            }
        }
        return $json;
    }

}