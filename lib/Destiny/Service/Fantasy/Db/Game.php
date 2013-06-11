<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;

class Game extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Service\Fantasy\Db\Game
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getRecentGameData() {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT games.gameId,games.aggregatedDate FROM dfl_games AS `games` 
			WHERE games.aggregated = 1
			ORDER BY games.aggregatedDate DESC 
			LIMIT 0,1' )->fetchRow ();
	}

	public function getGameById($gameId) {
		$db = Application::getInstance ()->getDb ();
		$game = $db->select ( '
				SELECT * FROM dfl_games AS `games` 
				WHERE games.gameId = \'{gameId}\' 
				ORDER BY games.gameCreatedDate DESC ', array (
				'gameId' => ( int ) $gameId 
		) )->fetchRow ();
		if (true == empty ( $game )) {
			throw new \Exception ( 'Game data not found' );
		}
		return $game;
	}

	public function getTrackedProgress($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					ingame.gameId,
					ingame.gameStartTime,
					ingame.gameData 
				FROM dfl_ingame_progress AS `ingame` 
				ORDER BY ingame.gameId DESC 
				LIMIT {offset},{limit}', array (
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	public function getGames($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT * FROM dfl_games AS `games` 
				ORDER BY games.gameId DESC 
				LIMIT {offset},{limit}', array (
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	public function getUnaggregatedGames($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT * FROM dfl_games AS `games` 
				WHERE games.aggregated = \'0\' 
				ORDER BY games.gameCreatedDate ASC 
				LIMIT {offset},{limit}', array (
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	public function getRecentGames($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT games.* FROM dfl_games AS `games` 
				WHERE games.aggregated = \'1\' 
				ORDER BY games.gameCreatedDate DESC 
				LIMIT {offset},{limit}', array (
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	public function getGameChampions($gameId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT gameChamps.*,champs.championName FROM dfl_games_champs AS `gameChamps` 
				INNER JOIN `dfl_champs` AS `champs` ON (champs.championId = gameChamps.championId) 
				WHERE gameChamps.gameId = \'{gameId}\' 
				ORDER BY gameChamps.teamSideId ASC, champs.championName ASC ', array (
				'gameId' => ( int ) $gameId 
		) )->fetchRows ();
	}

	public function getTeamGameChampionsScores(array $games, $teamId) {
		$gameIds = array ();
		foreach ( $games as $i => $gameId ) {
			$gameIds [] = $gameId ['gameId'];
		}
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					teamchampscores.gameId,
					teamchampscores.teamId,
					teamchampscores.championId,
					champs.championName,
					SUM(teamchampscores.scoreValue) AS `scoreValue`
				FROM dfl_scores_teams_champs AS `teamchampscores` 
				INNER JOIN `dfl_champs` AS `champs` ON (champs.championId = teamchampscores.championId) 
				WHERE teamchampscores.gameId IN ({gameIds}) AND teamchampscores.teamId = \'{teamId}\'
				GROUP BY teamchampscores.championId, teamchampscores.gameId
				', array (
				'gameIds' => join ( ',', $gameIds ),
				'teamId' => ( int ) $teamId 
		) )->fetchRows ();
	}

}
