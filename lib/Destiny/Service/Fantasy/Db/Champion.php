<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;

class Champion extends Service {
	
	/**
	 *
	 * @var ServiceFantasyDbChampion
	 */
	protected static $instance = null;
	protected $champions = array ();
	protected $freeChampions = array ();

	/**
	 *
	 * @return Service\Fantasy\Db\Champion
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getChampions() {
		if (empty ( $this->champions )) {
			$db = Application::getInstance ()->getDb ();
			$this->champions = $db->select ( 'SELECT * FROM dfl_champs AS `champs` ORDER BY champs.championName ASC ' )->fetchRows ();
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
		$db = Application::getInstance ()->getDb ();
		$db->insert ( 'INSERT INTO dfl_users_champs SET userId=\'{userId}\',championId=\'{championId}\',createdDate=UTC_TIMESTAMP()', array (
				'userId' => ( int ) $userId,
				'championId' => ( int ) $championId 
		) );
		return true;
	}

	public function getUserChampions($userId) {
		$db = Application::getInstance ()->getDb ();
		$champions = $db->select ( 'SELECT champs.* FROM dfl_users_champs AS `userchamps` ' . 'INNER JOIN dfl_champs AS `champs` ON (champs.championId = userchamps.championId) ' . 'WHERE userchamps.userId = \'{userId}\' ' . 'ORDER BY champs.championName ASC ', array (
				'userId' => $userId 
		) )->fetchRows ();
		return $champions;
	}

	public function updateFreeChampions() {
		$db = Application::getInstance ()->getDb ();
		$champs = $db->select ( '
			SELECT champs.championId 
			FROM dfl_champs AS `champs` 
			WHERE champs.championFree = 0
			AND champs.championId NOT IN (
				SELECT _champs.championId FROM dfl_champs AS `_champs` WHERE _champs.championFree = 1
			)
			ORDER BY RAND() 
			LIMIT 0,{limit}', array (
				'limit' => Config::$a ['fantasy'] ['maxFreeChamps'] 
		) )->fetchRows ();
		$ids = array ();
		foreach ( $champs as $champ ) {
			$ids [] = $champ ['championId'];
		}
		$db->query ( 'UPDATE dfl_champs SET championFree = \'0\' WHERE championFree = \'1\'' );
		$db->query ( 'UPDATE dfl_champs SET championFree = \'1\' WHERE championId IN ({freeChamps}) ', array (
				'freeChamps' => join ( ',', $ids ) 
		) );
	}

}
