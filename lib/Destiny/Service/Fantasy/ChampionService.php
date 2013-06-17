<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\Utils\Cache;

class ChampionService extends Service {
	
	/**
	 *
	 * @var ChampionService
	 */
	protected static $instance = null;
	protected $champions = array ();
	protected $freeChampions = array ();

	/**
	 *
	 * @return ChampionService
	 */
	public static function instance() {
		return parent::instance ();
	}

	public function getChampions() {
		if (empty ( $this->champions )) {
			$conn = Application::instance ()->getConnection ();
			$stmt = $conn->prepare ( 'SELECT * FROM dfl_champs AS `champs` ORDER BY champs.championName ASC' );
			$stmt->execute ();
			$this->champions = $stmt->fetchAll ();
		}
		return $this->champions;
	}

	public function getChampionById($id) {
		$champions = $this->getChampions ();
		// need to loop through id's first, to retain display order.
		foreach ( $champions as $champ ) {
			if (( int ) $champ ['championId'] == ( int ) $id) {
				return $champ;
			}
		}
		return null;
	}

	public function getChampionsById(array $ids) {
		$champions = $this->getChampions ();
		$filtered = array ();
		// need to loop through id's first, to retain display order.
		foreach ( $ids as $id ) {
			foreach ( $champions as $champ ) {
				if (( int ) $champ ['championId'] == ( int ) $id) {
					$filtered [] = $champ;
					break;
				}
			}
		}
		return $filtered;
	}

	public function unlockChampion($userId, $championId) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_users_champs', array (
				'userId' => $userId,
				'championId' => $championId,
				'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		), array (
				\PDO::PARAM_INT,
				\PDO::PARAM_INT,
				\PDO::PARAM_STR 
		) );
	}

	public function getUserChampions($userId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT champs.* 
			FROM dfl_users_champs AS `userchamps` 
			INNER JOIN dfl_champs AS `champs` ON (champs.championId = userchamps.championId) 
			WHERE userchamps.userId = :userId 
			ORDER BY champs.championName ASC 
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function updateFreeChampions() {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT champs.championId 
			FROM dfl_champs AS `champs` 
			WHERE champs.championFree = 0
			ORDER BY RAND() 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'limit', Config::$a ['fantasy'] ['maxFreeChamps'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', 0, \PDO::PARAM_INT );
		$stmt->execute ();
		$champs = $stmt->fetchAll ();
		$ids = array ();
		foreach ( $champs as $champ ) {
			$ids [] = $champ ['championId'];
		}
		
		// Set all the existing free champs to not free
		$conn->update ( 'dfl_champs', array (
				'championFree' => false 
		), array (
				'championFree' => true 
		), array (
				\PDO::PARAM_BOOL,
				\PDO::PARAM_BOOL 
		) );
		// Update the selected champs to free
		$stmt = $conn->prepare ( 'UPDATE dfl_champs SET championFree = :championFree WHERE championId IN (' . join ( ',', $ids ) . ')' );
		$stmt->bindValue ( 'championFree', true, \PDO::PARAM_BOOL );
		$stmt->execute ();
	}

}
