<?php
namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;
use Destiny\Config;
use \PDO;

class ChatlogService extends Service {
	
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

	/**
	 * Returns a new ChatLog
	 *
	 * @return \Destiny\Service\ChatLog
	 */
	public function getChatLog($limit) {
		return new ChatLog ( $limit );
	}

}
class ChatLog implements \Iterator {
	
	protected $stmt;
	protected $key = - 1;
	protected $value;
	protected $limit = 0;

	public function __construct($limit) {
		$this->limit = $limit;
	}

	public function rewind() {
		$conn = Application::instance ()->getConnection ();
		$this->stmt = $conn->prepare ( '
			SELECT
				u.userId,
				u.username,
				u2.username AS target,
				l.event,
				l.data,
				l.timestamp,
				(
				
					SELECT GROUP_CONCAT(fn.featureName)
					FROM dfl_users_features AS uf
					INNER JOIN dfl_features AS fn ON (fn.featureId = uf.featureId)
					WHERE uf.userId = u.userId
					
				) AS `features`
			FROM
				chatlog AS l
				LEFT JOIN dfl_users AS u ON u.userId = l.userid
				LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
			WHERE
				l.event NOT IN("JOIN", "QUIT")
			ORDER BY l.id DESC
			LIMIT ' . $this->limit, array (
			PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY 
		) );
		
		$this->stmt->execute ();
		$this->next ();
	}

	public function current() {
		return $this->value;
	}

	public function key() {
		return $this->key;
	}

	public function next() {
		$this->value = $this->stmt->fetch ( PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT );
		$this->key ++;
	}

	public function valid() {
		return $this->value !== false;
	}

}