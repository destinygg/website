<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Service\Fantasy\GameService;

class LeaderboardService extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return LeaderboardService
	 */
	public static function instance() {
		return parent::instance ();
	}

	public function getTeamLeaderboardByGame($gameId, $limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.userId, 
				users.username, 
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				users.country, 
				ranks.teamRank, 
				teams.*, 
				SUM(scoreteams.scoreValue) AS `sumScore`, 
				( 
					SELECT SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions)
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = teams.teamId
				) AS `champions` 
			FROM dfl_scores_teams AS `scoreteams`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = scoreteams.teamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\')
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
			WHERE scoreteams.gameId = :gameId
			GROUP BY scoreteams.teamId
			ORDER BY `sumScore` DESC, ranks.teamRank ASC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getTeamLeaderboard($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.userId, 
				users.username, 
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				users.country, 
				ranks.teamRank, 
				teams.*, 
				SUM(teams.scoreValue) AS `scoreValue`, 
				( 
					SELECT SUBSTRING_INDEX( GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions)
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = teams.teamId
				) AS `champions` 
			FROM dfl_teams AS `teams` 
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\') 
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
			GROUP BY teams.teamId 
			ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 END, ranks.teamRank ASC 
			LIMIT :offset,:limit 
		' );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getSubscriberTeamLeaderboard($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.userId, 
				users.username, 
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				users.country, 
				ranks.teamRank, 
				teams.*, 
				SUM(teams.scoreValue) AS `scoreValue`, 
				( 
					SELECT SUBSTRING_INDEX( GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions)
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = teams.teamId
				) AS `champions` 
			FROM dfl_teams AS `teams` 
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\') 
			INNER JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
			GROUP BY teams.teamId 
			ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 END, ranks.teamRank ASC 
			LIMIT :offset,:limit 
		' );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getTopSummoners($limit, $offset = 0) {
		$excludeId = array ();
		foreach ( Config::$a ['lol'] ['summoners'] as $summoner ) {
			$excludeId [] = $summoner ['id'];
		}
		
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				gameschamps.summonerId,
				gameschamps.summonerName,
				COUNT(gameschamps.gameId) AS `gamesPlayed`,
				(
					SELECT COUNT(a.gameId) AS `gameCount`
					FROM dfl_games_champs AS `a`
					INNER JOIN dfl_games AS  `b` ON (b.gameId = a.gameId)
					WHERE a.summonerId = gameschamps.summonerId AND b.gameWinSideId = gameschamps.teamSideId
				
				) AS `gamesWon`,
				(
					SELECT COUNT(a.gameId) AS `gameCount`
					FROM dfl_games_champs AS `a`
					INNER JOIN dfl_games AS  `b` ON (b.gameId = a.gameId)
					WHERE a.summonerId = gameschamps.summonerId AND b.gameWinSideId != gameschamps.teamSideId
				
				) AS `gamesLost`,
				(
			
					SELECT 
						b.championId
					FROM dfl_games_champs AS `a`
					INNER JOIN dfl_champs AS `b` ON (b.championId = a.championId)
					WHERE a.summonerId = gameschamps.summonerId
					GROUP BY a.summonerId, a.championId
					ORDER BY COUNT(*) DESC
					LIMIT 0,1
				
				) AS `mostPlayedChampion`
				
			FROM dfl_games_champs AS `gameschamps`
			WHERE summonerId NOT IN (' . join ( ',', $excludeId ) . ')
			GROUP BY gameschamps.summonerId
			ORDER BY `gamesPlayed` DESC, gameschamps.summonerId
			LIMIT :offset,:limit
		' );
		
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getRecentGameLeaderboard($limit, $offset = 0) {
		$game = GameService::instance ()->getRecentGameData ();
		if (empty ( $game )) {
			return array ();
		}
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.userId, 
				users.username, 
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				users.country, 
				ranks.teamRank, 
				teams.*, 
				SUM(scoreteams.scoreValue) AS `sumScore`, 
				( 
					SELECT SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions)
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = teams.teamId
				) AS `champions` 
			FROM dfl_scores_teams AS `scoreteams`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = scoreteams.teamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\')
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
			WHERE scoreteams.gameId = :gameId
			GROUP BY scoreteams.teamId
			ORDER BY `sumScore` DESC, ranks.teamRank ASC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'gameId', $game ['gameId'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getTopTeamChampionScores($limit, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				SUM(tcscores.scoreValue) AS `scoreValueSum`,
				tcscores.championId, 
				champs.* 
			FROM dfl_scores_teams_champs AS `tcscores`
			INNER JOIN dfl_champs AS `champs` ON (champs.championId = tcscores.championId)
			GROUP BY tcscores.championId
			ORDER BY scoreValueSum DESC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a list of team game scores, sorted by created date desc
	 *
	 * @param int $teamId
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getTeamGameScores($teamId, $limit = 10, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				ts.gameId,
				ts.teamId,
				SUM(ts.scoreValue) AS `scoreValue`,
				g.gameCreatedDate,
				g.gameEndDate
			FROM dfl_scores_teams AS ts
			INNER JOIN dfl_games AS g ON (g.gameId = ts.gameId)
			WHERE teamId = :teamId
			GROUP BY ts.gameId
			ORDER BY g.gameCreatedDate DESC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a list of team champ scores, sorted by created date desc
	 *
	 * @param int $teamId
	 * @param array $games
	 * @return array
	 */
	public function getTeamGameChampScores($games) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT a.*,c.championName FROM dfl_games_champs AS a
			INNER JOIN dfl_champs AS c ON (c.championId = a.championId)
			WHERE a.gameId IN (' . join ( ',', $games ) . ')
			ORDER BY a.gameId DESC, a.teamSideId DESC, c.championName DESC
		' );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a list of team champ scores, sorted by created date desc
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getTeamTopChampions($teamId, $limit = 10, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT a.teamId,a.championId,SUM(a.scoreValue) `scoreValue`,COUNT(*) AS `gamesPlayed`,c.championName FROM dfl_scores_teams_champs AS a
			INNER JOIN dfl_champs AS c ON (c.championId = a.championId)
			WHERE a.teamId = :teamId
			GROUP BY a.championId
			ORDER BY scoreValue DESC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

}