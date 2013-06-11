<?php

namespace Destiny\Service\Twitch;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;

class Subscription extends Service {
	
	/**
	 *
	 * @var Service
	 */
	protected static $instance = null;
	protected $token = '';

	/**
	 *
	 * @return ServiceTwitchSubscription
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Get the broadcasters latest token
	 *
	 * @return string
	 */
	private function getBroadcasterToken() {
		if (empty ( $this->token )) {
			$this->token = trim ( file_get_contents ( 'BROADCASTERTOKEN' ) );
		}
		return $this->token;
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