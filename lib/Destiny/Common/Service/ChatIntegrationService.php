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
	 * @param SessionInstance $session
	 * @param SessionCredentials $credentials
	 */
	public function setChatSession(SessionInstance $session, SessionCredentials $credentials) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$json = json_encode ( $credentials->getData () );
			$id = sprintf ( 'CHAT:%s', $session->getSessionId () );
			$update = ($redis->get ( $id )) ? true : false;
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->set ( $id, $json, 30 * 24 * 60 * 60 );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
			if ($update) {
				$this->refreshChatSession ( $credentials );
			}
		}
	}

	/**
	 * Refresh a users session
	 * @param SessionCredentials $credentials
	 */
	public function refreshChatSession(SessionCredentials $credentials) {
		$redis = Application::instance ()->getRedis ();
		if (! empty ( $redis )) {
			$json = json_encode ( $credentials->getData () );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );
			$redis->publish ( 'refreshuser', $json );
			$redis->setOption ( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP );
			$log = Application::instance ()->getLogger ();
			$log->notice ( 'Got refreshuser message [%s]', array (
				'cred' => $credentials->getData (),
				'session' => Session::getSessionId () 
			) );
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