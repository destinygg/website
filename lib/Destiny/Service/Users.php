<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;

class Users extends Service {
	
	/**
	 * Users service
	 *
	 * @var Service\Users
	 */
	protected static $instance = null;

	/**
	 * Get the singleton instance
	 *
	 * @return Service\Users
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Get the user record by userId
	 *
	 * @param string $externalId
	 */
	public function getUserById($userId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM `dfl_users` WHERE userId = \'{userId}\'', array (
				'userId' => $userId 
		) )->fetchRow ();
	}

	/**
	 * Get the user record by external Id
	 *
	 * @param string $externalId
	 */
	public function getUserByExternalId($externalId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( 'SELECT * FROM `dfl_users` WHERE externalId = \'{externalId}\'', array (
				'externalId' => $externalId 
		) )->fetchRow ();
	}

	/**
	 * Add a new user
	 *
	 * @param array $user
	 */
	public function addUser(array $user) {
		$db = Application::getInstance ()->getDb ();
		$user ['userId'] = $db->insert ( '
			INSERT INTO dfl_users SET
				externalId = \'{externalId}\',
				username = \'{username}\',
				displayName = \'{displayName}\',
				country = \'{country}\',
				email = \'{email}\',
				admin = \'{admin}\',
				createdDate = UTC_TIMESTAMP()
		', array (
				'externalId' => $user ['externalId'],
				'username' => $user ['username'],
				'displayName' => $user ['displayName'],
				'country' => $user ['country'],
				'email' => $user ['email'],
				'admin' => ((( boolean ) $user ['admin']) ? '1' : '0') 
		) );
		return $user;
	}

	/**
	 * Update an existing user by userId
	 *
	 * @param array $user
	 */
	public function updateUser(array $user) {
		$db = Application::getInstance ()->getDb ();
		$db->query ( '
			UPDATE dfl_users SET
				externalId = \'{externalId}\',
				displayName = \'{displayName}\',
				username = \'{username}\',
				email = \'{email}\',
				country = \'{country}\',
				admin = \'{admin}\'
			WHERE
				userId = \'{userId}\'
		', array (
				'userId' => $user ['userId'],
				'externalId' => $user ['externalId'],
				'displayName' => $user ['displayName'],
				'username' => $user ['username'],
				'email' => $user ['email'],
				'country' => $user ['country'],
				'admin' => $user ['admin'] 
		) );
		return $user;
	}

}