<?php
namespace Destiny\Chat;

use Destiny\Common\Session;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\SessionCredentials;
use Destiny\Common\Config;
use Destiny\Common\Exception;

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
	 * Refreshes the current users session timeout
	 *
	 * @param string $sessionId        	
	 * @return void
	 */
	public function renewChatSessionExpiration($sessionId) {
		if (! empty ( $sessionId )) {
			$redis = Application::instance ()->getRedis ();
			$id = sprintf ( 'CHAT:session-%s', $sessionId );
			if (! empty ( $redis )) {
				$redis->expire ( $id, intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
			}
		}
	}

	/**
	 * Handle the update of the credentials for chat
	 *
	 * @param SessionCredentials $credentials        	
	 * @param string $sessionId        	
	 */
	public function setChatSession(SessionCredentials $credentials, $sessionId) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$json = json_encode ( $credentials->getData () );
			$id = sprintf ( 'CHAT:session-%s', $sessionId );
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
			$redis->publish ( sprintf ( 'refreshuser-%s', Config::$a ['redis'] ['database'] ), $json );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
		}
	}

	/**
	 * Delete the session for the chat user
	 */
	public function deleteChatSession() {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$redis->delete ( sprintf ( 'CHAT:session-%s', Session::getSessionId () ) );
		}
	}

	/**
	 * Broadcast a message
	 *
	 * @param string $message
	 *        	the message
	 * @throws Exception
	 */
	public function sendBroadcast($message) {
		if (empty ( $message )) {
			throw new Exception ( 'Message required' );
		}
		$redis = Application::instance ()->getRedis ();
		$broadcast = new \stdClass ();
		$broadcast->data = $message;
		if (! empty ( $redis )) {
			$redis->publish ( sprintf ( 'broadcast-%s', Config::$a ['redis'] ['database'] ), json_encode ( $broadcast ) );
		}
		return $broadcast;
	}

	/**
	 * Unban and unmute a userId
	 *
	 * @param int $userId
	 *        	the userId
	 * @throws Exception
	 */
	public function sendUnban($userId) {
		if (!$userId) {
			throw new Exception ( 'UserId required' );
		}
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$redis->publish ( sprintf ( 'unbanuserid-%s', Config::$a ['redis'] ['database'] ), (string)$userId );
		}
		return $userId;
	}

}