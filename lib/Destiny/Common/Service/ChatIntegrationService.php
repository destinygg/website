<?php
namespace Destiny\Common\Service;

use Destiny\Common\Session;
use Destiny\Common\UserRole;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\SessionInstance;
use Destiny\Common\SessionCredentials;

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
	 * @param SessionCredentials $credentials
	 * @param string $sessionId
	 */
	public function setChatSession(SessionCredentials $credentials, $sessionId) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$json = json_encode ( $credentials->getData () );
			$id = sprintf ( 'CHAT:%s', $sessionId );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->set ( $id, $json, intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
		}
	}

	/**
	 * Update a users session
	 *
	 * @param SessionCredentials $credentials
	 */
	public function refreshChatUserSession(SessionCredentials $credentials) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$json = json_encode ( $credentials->getData () );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->publish ( 'refreshuser', $json );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
		}
	}

	/**
	 * Delete the session for the chat user
	 */
	public function deleteChatSession() {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$redis->delete ( sprintf ( 'CHAT:%s', Session::getSessionId () ) );
		}
	}

}