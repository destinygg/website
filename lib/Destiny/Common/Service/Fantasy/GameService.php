<?php
namespace Destiny\Common\Service\Fantasy;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Cache;
use Destiny\Common\Exception;

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

	/**
	 * We are limiting this to 0,1, but in reality, if you track multiple people and they play in the same game, you will have more than one tracked game
	 *
	 * @param int $gameId
	 * @return array
	 */
	public function getTrackedProgressByGameId($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				ingame.gameId,
				ingame.gameStartTime,
				ingame.gameData 
			FROM dfl_ingame_progress AS `ingame` 
			WHERE ingame.gameId = :gameId
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

	public function getTeamGameChampionsScores($teamId, $gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				teamchampscores.gameId,
				teamchampscores.teamId,
				teamchampscores.championId,
				champs.championName,
				teamchampscores.championMultiplier,
				teamchampscores.penalty,
				SUM(teamchampscores.scoreValue) AS `scoreValue`
			FROM dfl_scores_teams_champs AS `teamchampscores` 
			INNER JOIN `dfl_champs` AS `champs` ON (champs.championId = teamchampscores.championId) 
			WHERE teamchampscores.gameId = :gameId AND teamchampscores.teamId = :teamId AND scoreValue != 0
			GROUP BY teamchampscores.championId, teamchampscores.gameId
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getTeamGameScores($teamId, $gameId, $excludeType = '') {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT
				s.gameId,
				s.teamId,
				s.scoreType,
				s.scoreValue
			FROM dfl_scores_teams AS `s`
			WHERE s.gameId = :gameId AND s.teamId = :teamId AND s.scoreType != :excludeType
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'excludeType', $excludeType, \PDO::PARAM_STR );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}
