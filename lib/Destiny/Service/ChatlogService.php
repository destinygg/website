<?php
namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Color;
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
		
		$conn = Application::instance ()->getConnection ();
		
		//$logger = new \Doctrine\DBAL\Logging\EchoSQLLogger();
		//$conn->getConfiguration()->setSQLLogger($logger);
		
		$stmt = $conn->prepare ( '
			SELECT
				u.username,
				u2.username AS target,
				l.event,
				l.data,
				l.timestamp,
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
			WHERE
				l.event NOT IN("JOIN", "QUIT")
			ORDER BY l.id DESC
			LIMIT ' . $limit
		);
		
		$stmt->execute();
		$chatlog  = $stmt->fetchAll();
		$lines    = array();
		$suppress = array();
		
		foreach ( $chatlog as &$line ) {
			
			if ( $line['event'] == 'MUTE' or $line['event'] == 'BAN' ) {
				$suppress[ $line['target'] ] = true;
			}
			
			if ( isset( $suppress[ $line['username'] ] ) ) {
				continue;
			}
			
			if (! empty ( $line ['features'] )) {
				$line ['features'] = explode ( ',', $line ['features'] );
				$line ['color'] = Color::getFeaturesColor ( $line ['features'] );
			}
			
			$lines[] = $line;
			
		}
		
		return $lines;
		
	}

}
