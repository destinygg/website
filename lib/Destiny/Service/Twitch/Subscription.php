<?php
namespace Destiny\Service\Twitch;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;

class Subscription extends Service {
	
	/**
	 * @var Service
	 */
	protected static $instance = null;
	protected $token = '';

	/**
	 * @return ServiceTwitchSubscription
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * @return string
	 */
	private function getBroadcasterToken() {
		if (empty ( $this->token )) {
			$db = Application::getInstance ()->getDb ();
			$this->token = $db->select ( '
				SELECT a.token FROM dfl_users_sessions `a`
				INNER JOIN dfl_users `b` ON (a.userId = b.userId)
				WHERE b.username = \'{username}\'
				ORDER BY b.createdDate DESC
				LIMIT 0,1
			', array (
					'username' => Config::$a ['twitch'] ['broadcaster'] ['user'] 
			) )->fetchValue ();
		}
		return $this->token;
	}

	public function checkSubscription($username) {
		$token = $this->getBroadcasterToken ();
		if (! empty ( $token )) {
			$curl = curl_init ();
			curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/channels/' . urlencode ( Config::$a ['twitch'] ['broadcaster'] ['user'] ) . '/subscriptions/' . urlencode ( $username ) . '?oauth_token=' . $token );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
			curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
			$data = curl_exec ( $curl );
			$info = curl_getinfo ( $curl );
			$data = json_decode ( $data );
			return ($data != null && isset ( $data->user )) ? true : false;
		}
		return false;
	}

	public function getChannelSubscriptions($channel, $limit, $offset) {
		$token = $this->getBroadcasterToken ();
		if (! empty ( $token )) {
			$curl = curl_init ();
			curl_setopt ( $curl, CURLOPT_URL, 'https://api.twitch.tv/kraken/channels/' . urlencode ( $channel ) . '/subscriptions?limit='. intval($limit) .'&offset='. intval($offset) .'&oauth_token=' . $token );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $curl, CURLOPT_TIMEOUT, 25 );
			curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
			$data = curl_exec ( $curl );
			$info = curl_getinfo ( $curl );
			$data = json_decode ( $data );
			return $data;
		}
		return false;
	}

}