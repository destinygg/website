<?php
namespace Destiny\Common\Service;

use Destiny\Common\UserFeature;
use Destiny\Common\Service;
use Destiny\Common\Application;
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
	 * Returns a chat log list
	 *
	 * @return array
	 */
	public function getChatLog($limit) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT
				u.username,
				u2.username AS target,
				l.event,
				l.data,
				l.timestamp,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				(
				
					SELECT GROUP_CONCAT( DISTINCT fn.featureName)
					FROM dfl_users_features AS uf
					INNER JOIN dfl_features AS fn ON (fn.featureId = uf.featureId)
					WHERE uf.userId = u.userId
					ORDER BY fn.featureId ASC
					
				) AS `features`
			FROM
				chatlog AS l
				LEFT JOIN dfl_users AS u ON u.userId = l.userid
				LEFT JOIN dfl_users AS u2 ON u2.userId = l.targetuserid
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = u.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
			WHERE
				l.event NOT IN("JOIN", "QUIT")
			ORDER BY l.id DESC
			LIMIT ' . $limit );
		
		$stmt->execute ();
		$chatlog = $stmt->fetchAll ();
		$lines = array ();
		$suppress = array ();
		
		foreach ( $chatlog as &$line ) {
			
			if ($line ['event'] == 'MUTE' or $line ['event'] == 'BAN') {
				$suppress [$line ['target']] = true;
			}
			
			if (isset ( $suppress [$line ['username']] )) {
				continue;
			}
			
			if (! empty ( $line ['features'] )) {
				$line ['features'] = array_merge ( explode ( ',', $line ['features'] ) );
			} else {
				$line ['features'] = array ();
			}
			
			if (! empty ( $line ['subscriber'] ) && $line ['subscriber'] == 1) {
				$line ['features'] [] = UserFeature::SUBSCRIBER;
			}
			
			$lines [] = $line;
		}
		
		return $lines;
	}

}
