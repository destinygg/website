<?php
namespace Destiny\Common\Service;

use Destiny\Common\UserFeature;
use Destiny\Common\Service;
use Destiny\Common\Application;
use \PDO;

class ChatBanService extends Service {
	
	/**
	 * Singleton
	 *
	 * @var ChatBanService
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return ChatBanService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Returns a list of bans
	 *
	 * @return array
	 */
	public function getUserActiveBan($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT
				b.id,
				b.userid,
				u.username,
				b.targetuserid,
				u2.username AS targetusername,
				b.ipaddress,
				b.reason,
				b.starttimestamp,
				b.endtimestamp
			FROM
				bans AS b
				INNER JOIN dfl_users AS u ON u.userId = b.userid
				INNER JOIN dfl_users AS u2 ON u2.userId = b.targetuserid
				
			WHERE b.targetuserid = :userId AND b.starttimestamp < NOW() AND b.endtimestamp > NOW() 
				
			ORDER BY b.id DESC
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get a chat ban by ID
	 *
	 * @param int $banId
	 * @return array
	 */
	public function getBanById($banId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT
				b.id,
				b.userid,
				u.username,
				b.targetuserid,
				u2.username AS targetusername,
				b.ipaddress,
				b.reason,
				b.starttimestamp,
				b.endtimestamp
			FROM
				bans AS b
				INNER JOIN dfl_users AS u ON u.userId = b.userid
				INNER JOIN dfl_users AS u2 ON u2.userId = b.targetuserid
			WHERE b.id = :id
			ORDER BY b.id DESC
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'id', $banId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * update an existing ban
	 * @param array $ban
	 */
	public function updateBan(array $ban) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'bans', $ban, array (
			'id' => $ban ['id'] 
		) );
	}

	/**
	 * Insert a new chat ban
	 * @param array $ban
	 */
	public function insertBan(array $ban) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'bans', $ban );
		return $conn->lastInsertId ();
	}

}
