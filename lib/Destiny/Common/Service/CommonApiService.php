<?php
namespace Destiny\Common\Service;

use Destiny\Common\Service;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\String;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\MimeType;

class CommonApiService extends Service {
	
	/**
	 * Singleton
	 *
	 * @return CommonApiService
	 */
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
	 * @return \Destiny\CurlBrowser
	 */
	public function getBlogPosts(array $options = array()) {
		return new CurlBrowser ( array_merge ( array (
			'timeout' => 25,
			'url' => new String ( 'http://blog.destiny.gg/?feed=json&limit={limit}', array (
				'limit' => 3 
			) ),
			'contentType' => MimeType::JSON,
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
		return new CurlBrowser ( array_merge ( array (
			'url' => new String ( 'http://ws.audioscrobbler.com/2.0/?api_key={apikey}&user={user}&method=user.getrecenttracks&limit=3&format=json', Config::$a ['lastfm'] ),
			'contentType' => MimeType::JSON,
			'onfetch' => function ($json) {
				if (! $json || isset ( $json ['error'] ) && $json ['error'] > 0 || count ( $json ['recenttracks'] ['track'] ) <= 0) {
					throw new Exception ( 'Error fetching tracks' );
				}
				foreach ( $json ['recenttracks'] ['track'] as $i => $track ) {
					// Timezone DST = -1
					if (! isset ( $track ['@attr'] ) || $track ['@attr'] ['nowplaying'] != true) {
						if (! empty ( $track ['date'] )) {
							$track ['date'] ['uts'] = $track ['date'] ['uts'];
							$json ['recenttracks'] ['track'] [$i] ['date'] ['uts]'] = $track ['date'] ['uts'];
							$json ['recenttracks'] ['track'] [$i] ['date_str'] = Date::getDateTime ( $track ['date'] ['uts'] )->format ( Date::FORMAT );
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
	 * @throws Exception
	 * @return \Destiny\CurlBrowser
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
						$item ['snippet'] ['publishedAt'] = Date::getDateTime ( $item ['snippet'] ['publishedAt'], Date::FORMAT );
					}
				} else {
					throw new Exception ( 'Youtube API Down' );
				}
				return $json;
			} 
		), $options ) );
	}

}