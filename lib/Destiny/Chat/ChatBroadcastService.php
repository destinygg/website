<?php
namespace Destiny\Chat;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;

class ChatBroadcastService extends Service {
	
	/**
	 * Singleton instance
	 *
	 * var ChatBroadcastService
	 */
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return ChatBroadcastService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Persist the broadcast
	 * 
	 * @param string $message        	
	 * @param int $sentBy        	
	 * @return string
	 */
	public function addBroadcast($message, $sentBy) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'broadcasts', array (
				'message' => $message,
				'sentBy' => $sentBy,
				'sentOn' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		), array (
				\PDO::PARAM_STR,
				\PDO::PARAM_INT,
				\PDO::PARAM_STR 
		) );
		return $conn->lastInsertId ();
	}

}