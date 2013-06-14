<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\AppException;

class GameService extends Service {
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return GameService
	 */
	public static function instance() {
		return parent::instance ();
	}

	public function getRecentGameData() {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT games.gameId,games.aggregatedDate FROM dfl_games AS `games` 
			WHERE games.aggregated = 1
			ORDER BY games.aggregatedDate DESC 
			LIMIT 0,1
		' );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	public function getGameById($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_games AS `games` 
			WHERE games.gameId = :gameId 
			ORDER BY games.gameCreatedDate DESC 
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	public function getTrackedProgress($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				ingame.gameId,
				ingame.gameStartTime,
				ingame.gameData 
			FROM dfl_ingame_progress AS `ingame` 
			ORDER BY ingame.gameId DESC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getGames($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_games AS `games` 
			ORDER BY games.gameId DESC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getUnaggregatedGames($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_games AS `games` 
			WHERE games.aggregated = \'0\' 
			ORDER BY games.gameCreatedDate ASC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getRecentGames($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT games.* FROM dfl_games AS `games` 
			WHERE games.aggregated = \'1\' 
			ORDER BY games.gameCreatedDate DESC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getGameChampions($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT gameChamps.*,champs.championName FROM dfl_games_champs AS `gameChamps` 
			INNER JOIN `dfl_champs` AS `champs` ON (champs.championId = gameChamps.championId) 
			WHERE gameChamps.gameId = :gameId
			ORDER BY gameChamps.teamSideId ASC, champs.championName ASC
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getTeamGameChampionsScores(array $games, $teamId) {
		$gameIds = array ();
		foreach ( $games as $i => $gameId ) {
			$gameIds [] = intval ( $gameId ['gameId'] );
		}
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				teamchampscores.gameId,
				teamchampscores.teamId,
				teamchampscores.championId,
				champs.championName,
				SUM(teamchampscores.scoreValue) AS `scoreValue`
			FROM dfl_scores_teams_champs AS `teamchampscores` 
			INNER JOIN `dfl_champs` AS `champs` ON (champs.championId = teamchampscores.championId) 
			WHERE teamchampscores.gameId IN (' . join ( ',', $gameIds ) . ') AND teamchampscores.teamId = :teamId
			GROUP BY teamchampscores.championId, teamchampscores.gameId
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}
