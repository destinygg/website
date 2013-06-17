<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\MimeType;
use Destiny\HttpApiConsumer;
use Destiny\Utils\String;
use Destiny\Utils\Date;
use Destiny\Service\TwitchApiService;

class TwitchApiService extends Service {
	
	/**
	 * Stored when the broadcaster logs in, used to retrieve subscription
	 *
	 * @var string
	 */
	protected $token = '';
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return TwitchApiService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Get the broadcasters latest token
	 *
	 * @return string
	 */
	private function getBroadcasterToken() {
		if (empty ( $this->token )) {
			$this->token = trim ( file_get_contents ( Config::$a ['cache'] ['path'] . 'BROADCASTERTOKEN.tmp' ) );
		}
		return $this->token;
	}

	/**
	 *
	 * @param array $options
	 * @return ApiConsumer
	 */
	public function getPastBroadcasts(array $options = array()) {
		return new HttpApiConsumer ( array_merge ( array (
				'timeout' => 25,
				'url' => new String ( 'https://api.twitch.tv/kraken/channels/{user}/videos?broadcasts=true&limit={limit}', array (
						'user' => Config::$a ['twitch'] ['user'],
						'limit' => 4 
				) ),
				'contentType' => MimeType::JSON 
		), $options ) );
	}

	/**
	 *
	 * @return ApiConsumer
	 */
	public function getStreamInfo(array $options = array()) {
		return new HttpApiConsumer ( array_merge ( array (
				'url' => new String ( 'https://api.twitch.tv/kraken/streams/{user}/', array (
						'user' => Config::$a ['twitch'] ['user'] 
				) ),
				'contentType' => MimeType::JSON,
				'onfetch' => function ($json) {
					if (is_object ( $json ) && isset ( $json ['stream'] ) && $json ['stream'] != null) {
						$json ['stream'] ['channel'] ['updated_at'] = Date::getDateTime ( $json ['stream'] ['channel'] ['updated_at'] )->format ( Date::FORMAT );
					}
					// Last broadcast if the stream is offline
					// Called via static method, because we are in a closure
					$channel = TwitchApiService::instance ()->getChannel ()->getResponse ();
					$json ['lastbroadcast'] = Date::getDateTime ( $channel ['updated_at'] )->format ( Date::FORMAT );
					$json ['status'] = $channel ['status'];
					$json ['game'] = $channel ['game'];
					
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
		return new HttpApiConsumer ( array_merge ( array (
				'url' => new String ( 'https://api.twitch.tv/kraken/channels/{user}', array (
						'user' => Config::$a ['twitch'] ['user'] 
				) ),
				'contentType' => MimeType::JSON 
		), $options ) );
	}

	/**
	 * Get channel subscription info from Twitch
	 *
	 * @param string $channel
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getChannelSubscriptions($channel, $limit, $offset) {
		$token = $this->getBroadcasterToken ();
		if (! empty ( $token )) {
			$curl = curl_init ();
			curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/channels/' . urlencode ( $channel ) . '/subscriptions?limit=' . intval ( $limit ) . '&offset=' . intval ( $offset ) . '&oauth_token=' . $token );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
			curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
			$data = curl_exec ( $curl );
			$info = curl_getinfo ( $curl );
			$data = json_decode ( $data, true );
			return $data;
		}
		return null;
	}

}