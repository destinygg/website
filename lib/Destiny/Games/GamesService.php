<?php
namespace Destiny\Games;

use Destiny\Common\Service;
use Destiny\Common\Application;

class GamesService extends Service {
	
	/**
	 * Singleton
	 *
	 * @return GamesService
	 */
	protected static $instance = null;
	
	/**
	 * An array of games
	 * 
	 * @var array
	 */
	protected $games = null;

	/**
	 * Singleton
	 *
	 * @return GamesService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Get the games from the games.json file
	 * 
	 * @return array
	 */
	public function getGames() {
		if ($this->games == null) {
			$cacheDriver = Application::instance ()->getCacheDriver ();
			$games = $cacheDriver->fetch ( 'games' );
			if (empty ( $games )) {
				$games = json_decode ( file_get_contents ( _BASEDIR . '/lib/Resources/games.json' ), true );
				$cacheDriver->save ( 'games', $games );
			}
			if (is_array ( $games )) {
				$this->games = $games;
			}
		}
		return $this->games;
	}

	/**
	 * Add a user game
	 * 
	 * @param int $userId        	
	 * @param int $gameId        	
	 * @return boolean
	 */
	public function addUserGame($userId, $gameId) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'users_games', array (
				'userId' => $userId,
				'gameId' => $gameId 
		), array (
				\PDO::PARAM_INT,
				\PDO::PARAM_INT 
		) );
		return $conn->lastInsertId ();
	}

	/**
	 * Remove a user game
	 * 
	 * @param int $userId        	
	 * @param int $gameId        	
	 * @return boolean
	 */
	public function removeUserGame($userId, $gameId) {
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'users_games', array (
				'userId' => $userId,
				'gameId' => $gameId 
		), array (
				\PDO::PARAM_INT,
				\PDO::PARAM_INT 
		) );
		return true;
	}

	/**
	 * Get the list of games for a user
	 * 
	 * @param int $userId        	
	 * @return array
	 */
	public function getUserGames($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT id,gameId FROM users_games WHERE userId = :userId' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}