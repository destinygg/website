<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Api\Consumer;
use Destiny\Utils\String;
use Destiny\Utils\Date;
use Destiny\AppException;

class Youtube extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return \Destiny\Service\Youtube
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getPlaylist(array $options = array(), array $params = array()) {
		$params ['limit'] = (isset ( $params ['limit'] )) ? intval ( $params ['limit'] ) : 4;
		return new Consumer ( array_merge ( array (
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