<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Api\Consumer;
use Destiny\Utils\String;
use Destiny\Utils\Date;
use Destiny\AppException;

class Lastfm extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Service\Lastfm
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Get the most recent LastFM tracks
	 *
	 * @param array $options
	 * @return \Destiny\ApiConsumer
	 */
	public function getRecentTracks(array $options = array()) {
		return new Consumer ( array_merge ( array (
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

}