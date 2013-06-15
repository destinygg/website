<?php

namespace Destiny\Service;

use Destiny\Service;
use Destiny\Application;
use Destiny\Utils\Date;

class UsersService extends Service {
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return UsersService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Get the user record by userId
	 *
	 * @param string $externalId
	 */
	public function getUserById($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE userId = :userId LIMIT 0,1' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Get the user record by external Id
	 *
	 * @param string $externalId
	 */
	public function getUserByExternalId($externalId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE externalId = :externalId LIMIT 0,1' );
		$stmt->bindValue ( 'externalId', $externalId, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Add a new user
	 *
	 * @param array $user
	 */
	public function addUser(array $user) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users', array (
				'externalId' => $user ['externalId'],
				'username' => $user ['username'],
				'displayName' => $user ['displayName'],
				'country' => $user ['country'],
				'email' => $user ['email'],
				'admin' => $user ['admin'],
				'createdDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		) );
		return $conn->lastInsertId ();
	}

	/**
	 * Update an existing user by userId
	 *
	 * @param array $user
	 */
	public function updateUser(array $user) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_users', array (
				'externalId' => $user ['externalId'],
				'username' => $user ['username'],
				'displayName' => $user ['displayName'],
				'country' => $user ['country'],
				'email' => $user ['email'],
				'admin' => $user ['admin'] 
		), array (
				'userId' => $user ['userId'] 
		) );
		return $user;
	}

}