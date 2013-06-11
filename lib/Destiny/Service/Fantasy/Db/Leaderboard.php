<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Service\Fantasy\Db\Game;

class Leaderboard extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Leaderboard
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getTeamLeaderboard($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					users.userId, 
					users.username, 
					users.displayName, 
					IF(subs.userId IS NULL,0,1) AS `subscriber`,
					users.country, 
					users.displayName, 
					ranks.teamRank, 
					teams.*, 
					SUM(teams.scoreValue) AS `scoreValue`, 
					( 
						SELECT 
						SUBSTRING_INDEX(
							GROUP_CONCAT(
								champs.championId ORDER BY champs.displayOrder ASC
							),\',\',{maxChampions}
						)
						FROM dfl_team_champs AS `champs`  
						WHERE champs.teamId = teams.teamId
					) AS `champions` 
				FROM dfl_teams AS `teams` 
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
				WHERE teams.teamActive = 1
				GROUP BY teams.teamId 
				ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 END, ranks.teamRank ASC 
				LIMIT {offset},{limit} ', array (
				'offset' => $offset,
				'limit' => $limit,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getSubscriberTeamLeaderboard($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					users.userId, 
					users.username, 
					users.displayName, 
					IF(subs.userId IS NULL,0,1) AS `subscriber`,
					users.country, 
					users.displayName, 
					ranks.teamRank, 
					teams.*, 
					SUM(teams.scoreValue) AS `scoreValue`, 
					( 
						SELECT 
						SUBSTRING_INDEX(
							GROUP_CONCAT(
								champs.championId ORDER BY champs.displayOrder ASC
							),\',\',{maxChampions}
						)
						FROM dfl_team_champs AS `champs`  
						WHERE champs.teamId = teams.teamId
					) AS `champions` 
				FROM dfl_teams AS `teams` 
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
				WHERE teams.teamActive = 1
				GROUP BY teams.teamId 
				ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 END, ranks.teamRank ASC 
				LIMIT {offset},{limit} ', array (
				'offset' => $offset,
				'limit' => $limit,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getTeamRangeLeaderboard($start, $end, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					users.userId, 
					users.username, 
					users.displayName, 
					IF(subs.userId IS NULL,0,1) AS `subscriber`,
					users.country, 
					teams.*, 
					SUM(scoreteams.scoreValue) AS `weekScore`, 
					( 
						SELECT 
						SUBSTRING_INDEX(
							GROUP_CONCAT(
								champs.championId ORDER BY champs.displayOrder ASC
							),\',\',{maxChampions}
						)
						FROM dfl_team_champs AS `champs`  
						WHERE champs.teamId = teams.teamId
					) AS `champions` 
				FROM dfl_scores_teams AS `scoreteams`
				INNER JOIN dfl_teams AS `teams` ON (teams.teamId = scoreteams.teamId)
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId)
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				WHERE teams.teamActive = 1 AND scoreteams.createdDate BETWEEN \'{start}\' AND \'{end}\'
				GROUP BY scoreteams.teamId
				ORDER BY `weekScore` DESC
				LIMIT {offset},{limit} ', array (
				'offset' => $offset,
				'limit' => $limit,
				'start' => $start,
				'end' => $end,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getTopSummoners($limit) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
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
			GROUP BY gameschamps.summonerId
			ORDER BY `gamesPlayed` DESC, gameschamps.summonerId
			LIMIT 0,{limit}', array (
				'limit' => $limit 
		) )->fetchRows ();
	}

	public function getTeamChampionScores($teamId, $limit) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT champs.*,SUM(tchamps.scoreValue) AS `scoreValueSum`
			FROM dfl_scores_teams_champs AS `tchamps`
			INNER JOIN dfl_champs AS `champs` ON (champs.championId = tchamps.championId)
			WHERE tchamps.teamId = {teamId}
			GROUP BY tchamps.championId
			ORDER BY scoreValueSum DESC
			LIMIT 0,{limit}', array (
				'limit' => $limit,
				'teamId' => $teamId 
		) )->fetchRows ();
	}

	public function getRecentGameLeaderboard($limit, $offset = 0) {
		$game = Game::getInstance ()->getRecentGameData ();
		if (empty ( $game )) {
			return array ();
		}
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					users.userId, 
					users.username, 
					users.displayName, 
					IF(subs.userId IS NULL,0,1) AS `subscriber`,
					users.country, 
					ranks.teamRank, 
					teams.*, 
					SUM(scoreteams.scoreValue) AS `sumScore`, 
					( 
						SELECT 
						SUBSTRING_INDEX(
							GROUP_CONCAT(
								champs.championId ORDER BY champs.displayOrder ASC
							),\',\',{maxChampions}
						)
						FROM dfl_team_champs AS `champs`  
						WHERE champs.teamId = teams.teamId
					) AS `champions` 
				FROM dfl_scores_teams AS `scoreteams`
				INNER JOIN dfl_teams AS `teams` ON (teams.teamId = scoreteams.teamId)
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId)
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId)  
				WHERE teams.teamActive = 1 AND scoreteams.gameId = \'{gameId}\'
				GROUP BY scoreteams.teamId
				ORDER BY `sumScore` DESC, ranks.teamRank ASC
				LIMIT {offset},{limit} 
				', array (
				'offset' => $offset,
				'limit' => $limit,
				'gameId' => $game ['gameId'],
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getTopTeamChampionScores($limit) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT 
				SUM(tcscores.scoreValue) AS `scoreValueSum`,
				tcscores.championId, 
				champs.* 
			FROM dfl_scores_teams_champs AS `tcscores`
			INNER JOIN dfl_champs AS `champs` ON (champs.championId = tcscores.championId)
			GROUP BY tcscores.championId
			ORDER BY scoreValueSum DESC
			LIMIT 0,{limit}', array (
				'limit' => $limit 
		) )->fetchRows ();
	}

}