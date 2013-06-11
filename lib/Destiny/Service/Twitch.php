<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Api\Consumer;
use Destiny\Utils\String;
use Destiny\Utils\Date;
use Destiny\Service\Twitch;

class Twitch extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return \Destiny\Service\Twitch
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 *
	 * @param array $options
	 * @return ApiConsumer
	 */
	public function getPastBroadcasts(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'timeout' => 25,
				'url' => new String ( 'https://api.twitch.tv/kraken/channels/{user}/videos?broadcasts=true&limit={limit}', array (
						'user' => Config::$a ['twitch'] ['user'],
						'limit' => 4 
				) ),
				'contentType' => Mimetype::JSON 
		), $options ) );
	}

	/**
	 *
	 * @return ApiConsumer
	 */
	public function getStreamInfo(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'url' => new String ( 'https://api.twitch.tv/kraken/streams/{user}/', array (
						'user' => Config::$a ['twitch'] ['user'] 
				) ),
				'contentType' => Mimetype::JSON,
				'onfetch' => function ($json) {
					if (is_object ( $json ) && isset ( $json ['stream'] ) && $json ['stream'] != null) {
						$json ['stream'] ['channel'] ['updated_at'] = Date::getDateTime ( $json ['stream'] ['channel'] ['updated_at'], Date::FORMAT );
					}
					// Last broadcast if the stream is offline
					$json ['lastbroadcast'] = null;
					if ($json ['stream'] == null) {
						$channel = Twitch::getInstance ()->getChannel ()->getResponse ();
						$json ['lastbroadcast'] = Date::getDateTime ( $channel ['updated_at'], Date::FORMAT );
					}
					// Just some clean up
					if (isset ( $json ['_links'] )) {
						unset ( $json ['_links'] );
					}
					return $json;
				} 
		), $options ) );
	}

	/**
	 *
	 * @param array $options
	 * @return ApiConsumer
	 */
	public function getChannel(array $options = array()) {
		return new Consumer ( array_merge ( array (
				'url' => new String ( 'https://api.twitch.tv/kraken/channels/{user}', array (
						'user' => Config::$a ['twitch'] ['user'] 
				) ),
				'contentType' => Mimetype::JSON 
		), $options ) );
	}

}