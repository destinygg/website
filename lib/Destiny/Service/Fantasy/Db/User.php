<?php
namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Utils\Date;

class User extends Service {
	
	protected static $instance = null;

	/**
	 * @return ServiceFantasyDbUser
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function persistUser(array &$user) {
		$db = Application::getInstance ()->getDb ();
		$user ['userId'] = $db->select ( 'SELECT userId FROM `dfl_users` WHERE externalId = \'{externalId}\'', array (
				'externalId' => $user ['externalId'] 
		) )->fetchValue ();
		if (( int ) $user ['userId'] <= 0) {
			$user ['userId'] = $db->insert ( '
				INSERT INTO dfl_users SET 
					externalId = \'{externalId}\',
					username = \'{username}\',
					displayName = \'{displayName}\',
					email = \'{email}\',
					admin = \'{admin}\',
					createdDate = UTC_TIMESTAMP()
				', array (
					'externalId' => $user ['externalId'],
					'username' => $user ['username'],
					'displayName' => $user ['displayName'],
					'email' => $user ['email'],
					'admin' => ((( boolean ) $user ['admin']) ? '1' : '0'),
			) );
		} else {
			$db->query ( '
				UPDATE dfl_users SET 
					username = \'{username}\',
					displayName = \'{displayName}\',
					email = \'{email}\'
				WHERE 
					externalId = \'{externalId}\'
				', array (
					'externalId' => $user ['externalId'],
					'username' => $user ['username'],
					'displayName' => $user ['displayName'],
					'email' => $user ['email']
			) );
		}
	}

	public function updateUser(array $user) {
		$db = Application::getInstance ()->getDb ();
		$db->query ( '
			UPDATE dfl_users SET 
				country = \'{country}\'
			WHERE 
				userId = \'{userId}\'
			', array (
				'userId' => $user ['userId'],
				'country' => $user ['country'] 
		) );
	}
	
}