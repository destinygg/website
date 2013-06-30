<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;
use Destiny\Config;
use \PDO;

class ChatlogService extends Service implements \Iterator {
	protected $stmt;
	protected $key = -1;
	protected $value;
	
	/**
	 * Singleton
	 *
	 * @var ChatlogService
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return ChatlogService
	 */
	public static function instance() {
		return parent::instance ();
	}
	
	public function rewind() {
		
		$conn = Application::instance ()->getConnection ();
		
		// TODO get the users features for the icons
		$this->stmt = $conn->prepare ( '
			SELECT
				u.username,
				u2.username AS target,
				l.event,
				l.data,
				l.timestamp
			FROM
				chatlog AS l
				LEFT JOIN dfl_users AS u ON u.userId = l.userid
				LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
			WHERE
				l.event NOT IN("JOIN", "QUIT")
			ORDER BY l.id ASC
			LIMIT ' . Config::$a['chatbacklog'],
			array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
		);
		
		$this->stmt->execute ();
		$this->next();
	}
	
	public function current() {
		return $this->value;
	}
	
	public function key() {
		return $this->key;
	}
	
	public function next() {
		$this->value = $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
		$this->key++;
	}
	
	public function valid() {
		return $this->value !== false;
	}
	
}