<?php
namespace Destiny\Service;

use Destiny\Application;
use Destiny\Service;
use Destiny\SessionInstance;
use Destiny\SessionCredentials;

class ChatIntegrationService extends Service {
	
	/**
	 * Singleton instance
	 *
	 * var ChatIntegrationService
	 */
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return ChatIntegrationService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Handle the update of the credentials for chat
	 * @param SessionInstance $session
	 * @param SessionCredentials $credentials
	 */
	public function updateSession(SessionInstance $session, SessionCredentials $credentials) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$data = json_encode ( $credentials->getData () );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->set ( sprintf ( 'CHAT:%s', $session->getSessionId () ), $data, 30 * 24 * 60 * 60 );
			$redis->publish ( 'refreshuser', $data );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
		}
	}

	/**
	 * Delete the session for the chat user
	 * @param SessionInstance $session
	 */
	public function deleteSession(SessionInstance $session) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$redis->delete ( sprintf ( 'CHAT:%s', $session->getSessionId () ) );
		}
	}

}