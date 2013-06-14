<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\HttpApiConsumer;
use Destiny\Utils\Date;
use Destiny\Utils\String;
use Destiny\Config;
use Destiny\AppException;
use Destiny\Mimetype;

class CommonApiService extends Service {
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return CommonApiService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Get the most recent blog posts
	 *
	 * @param array $options
	 * @return \Destiny\HttpApiConsumer
	 */
	public function getBlogPosts(array $options = array()) {
		return new HttpApiConsumer ( array_merge ( array (
				'timeout' => 25,
				'url' => new String ( 'http://www.destiny.gg/n/?feed=json&limit={limit}', array (
						'limit' => 3 
				) ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if ($json != null) {
						$json = array_slice ( $json, 0, 3 );
					}
					return $json;
				} 
		), $options ) );
	}

	/**
	 * Get the most recent LastFM tracks
	 *
	 * @param array $options
	 * @return \Destiny\ApiConsumer
	 */
	public function getLastFMTracks(array $options = array()) {
		return new HttpApiConsumer ( array_merge ( array (
				'url' => new String ( 'http://ws.audioscrobbler.com/2.0/?api_key={apikey}&user={user}&method=user.getrecenttracks&limit=3&format=json', Config::$a ['lastfm'] ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if (! $json || isset ( $json ['error'] ) && $json ['error'] > 0 || count ( $json ['recenttracks'] ['track'] ) <= 0) {
						throw new AppException ( 'Error fetching tracks' );
					}
					foreach ( $json ['recenttracks'] ['track'] as $i => $track ) {
						// Timezone DST = -1
						if (! isset ( $track ['@attr'] ) || $track ['@attr'] ['nowplaying'] != true) {
							if (! empty ( $track ['date'] )) {
								$track ['date'] ['uts'] = $track ['date'] ['uts'] + (Config::$a ['time'] ['DSTOffset'] * 60);
								$json ['recenttracks'] ['track'] [$i] ['date'] ['uts]'] = $track ['date'] ['uts'];
								$json ['recenttracks'] ['track'] [$i] ['date_str'] = Date::getDateTime ( $track ['date'] ['uts'], Date::FORMAT );
							}
						} else {
							$json ['recenttracks'] ['track'] [$i] ['date_str'] = '';
						}
					}
					return $json;
				} 
		), $options ) );
	}

	/**
	 * Get a the latest playlist from google
	 *
	 * @param array $options
	 * @param array $params
	 * @throws AppException
	 * @return \Destiny\HttpApiConsumer
	 */
	public function getYoutubePlaylist(array $options = array(), array $params = array()) {
		$params ['limit'] = (isset ( $params ['limit'] )) ? intval ( $params ['limit'] ) : 4;
		return new HttpApiConsumer ( array_merge ( array (
				'url' => new String ( 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId={playlistId}&key={apikey}&maxResults={limit}', array (
						'playlistId' => Config::$a ['youtube'] ['playlistId'],
						'apikey' => Config::$a ['youtube'] ['apikey'],
						'limit' => $params ['limit'] 
				) ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if (is_array ( $json ['items'] )) {
						foreach ( $json ['items'] as $i => $item ) {
							$item ['snippet'] ['publishedAt'] = Date::getDateTime ( $item ['snippet'] ['publishedAt'], Date::FORMAT );
						}
					} else {
						throw new AppException ( 'Youtube API Down' );
					}
					return $json;
				} 
		), $options ) );
	}

}