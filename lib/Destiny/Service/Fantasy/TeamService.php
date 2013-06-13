<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;

class TeamService extends Service {
	
	/**
	 * The static singleton instance
	 *
	 * @var TeamService
	 */
	protected static $instance = null;

	/**
	 * Create the singleton instance
	 *
	 * @return TeamService
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 * Setup a new team for a user
	 *
	 * @param string $userId
	 * @return array
	 */
	public function addTeam($userId, $credits, $transfers) {
		$db = Application::getInstance ()->getDb ();
		$team = array (
				'teamId' => null,
				'userId' => $userId,
				'credits' => Config::$a ['fantasy'] ['team'] ['startCredit'],
				'transfersRemaining' => Config::$a ['fantasy'] ['team'] ['startTransfers'],
				'scoreValue' => 0 
		);
		$team ['teamId'] = $db->insert ( '
			INSERT INTO dfl_teams SET 
				userId = \'{userId}\',
				credits = \'{credits}\',
				scoreValue = \'{scoreValue}\',
				transfersRemaining = \'{transfersRemaining}\',
				teamActive = 1,
				modifiedDate = UTC_TIMESTAMP(),
				createdDate = UTC_TIMESTAMP()', $team );
		return $team;
	}

	/**
	 * Get a team record by teamId
	 *
	 * @param string $teamId
	 */
	public function getTeamById($teamId) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT * FROM dfl_teams AS `teams` 
				WHERE teams.teamId = \'{teamId}\' 
				LIMIT {offset},{limit}', array (
				'teamId' => ( int ) $teamId,
				'offset' => 0,
				'limit' => 1 
		) )->fetchRow ();
	}

	/**
	 * Return a list of teams by the teams username
	 *
	 * @param string $username
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeamsByUsername($username, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					teams.*, 
					users.username, 
					ranks.teamRank 
				FROM dfl_teams AS `teams` 
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
				WHERE users.username = \'{username}\' 
				ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
				LIMIT {offset},{limit} 
				', array (
				'username' => $username,
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	/**
	 * Get a team by userId
	 *
	 * @param int $userId
	 * @return array | null
	 */
	public function getTeamByUserId($userId) {
		$teams = self::getTeamsByUserId ( $userId );
		return (isset ( $teams [0] )) ? $teams [0] : null;
	}

	/**
	 * Get a list of team by userId
	 *
	 * @param int $userId
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getTeamsByUserId($userId, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT teams.*, users.username, ranks.teamRank 
					FROM dfl_teams AS `teams` 
					INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
					LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
					WHERE teams.userId = \'{userId}\' 
					ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
					LIMIT {offset},{limit} ', array (
				'userId' => ( int ) $userId,
				'offset' => ( int ) $offset,
				'limit' => ( int ) $limit 
		) )->fetchRows ();
	}

	/**
	 * Get the teams transfers
	 *
	 * @param int $teamId
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeamTransfers($teamId, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT * FROM dfl_team_transfers AS `transfers` 
				INNER JOIN dfl_champs AS `champs` ON (champs.championId = transfers.championId) 
				WHERE transfers.teamId = \'{teamId}\' 
				ORDER BY transfers.createdDate DESC, FIELD(transferType, \'OUT\', \'IN\') 
				LIMIT {offset},{limit} ', array (
				'teamId' => $teamId,
				'offset' => $offset,
				'limit' => $limit 
		) )->fetchRows ();
	}

	/**
	 * Get a flat list of teams
	 *
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeams($limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					teams.*, 
					users.userId, 
					users.username, 
					users.displayName, 
					IF(subs.userId IS NULL,0,1) AS `subscriber` 
				FROM dfl_teams AS `teams` 
				INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
				ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
				LIMIT {offset},{limit} ', array (
				'offset' => $offset,
				'limit' => $limit 
		) )->fetchRows ();
	}

	/**
	 * Get the teams champions
	 *
	 * @param int $teamId
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeamChamps($teamId, $limit = null, $offset = 0) {
		if ($limit == null) {
			$limit = Config::$a ['fantasy'] ['team'] ['maxChampions'];
		}
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT teamchamps.*, champs.*, userchamps.championId IS NOT NULL AS `unlocked` FROM dfl_team_champs AS `teamchamps` 
				INNER JOIN `dfl_teams` AS `teams` ON (teams.teamId = teamchamps.teamId) 
				INNER JOIN `dfl_champs` AS `champs` ON (teamchamps.championId = champs.championId) 
				LEFT JOIN `dfl_users_champs` AS `userchamps` ON (userchamps.userId = teams.userId AND userchamps.championId = champs.championId) 
				WHERE teams.teamId = \'{teamId}\' 
				ORDER BY teamchamps.displayOrder, champs.championName DESC, teamchamps.createdDate LIMIT {offset},{limit}', array (
				'teamId' => $teamId,
				'offset' => $offset,
				'limit' => $limit 
		) )->fetchRows ();
	}

	/**
	 * Reset and remove team
	 *
	 * @param array $team
	 */
	public function resetTeam(array $team) {
		$db = Application::getInstance ()->getDb ();
		$team ['credits'] = Config::$a ['fantasy'] ['team'] ['startCredit'];
		$team ['transfersRemaining'] = Config::$a ['fantasy'] ['team'] ['startTransfers'];
		$team ['scoreValue'] = 0;
		$this->updateTeam ( $team );
		$db->query ( 'DELETE FROM dfl_team_ranks WHERE teamId = \'{teamId}\'', array (
				'teamId' => ( int ) $team ['teamId'] 
		) );
		$db->query ( 'DELETE FROM dfl_users_champs WHERE userId = \'{userId}\'', array (
				'userId' => ( int ) $team ['userId'] 
		) );
		$db->query ( 'DELETE FROM dfl_team_champs WHERE teamId = \'{teamId}\'', array (
				'teamId' => ( int ) $team ['teamId'] 
		) );
		$db->query ( 'DELETE FROM dfl_team_milestones WHERE teamId = \'{teamId}\'', array (
				'teamId' => ( int ) $team ['teamId'] 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams WHERE teamId = \'{teamId}\'', array (
				'teamId' => ( int ) $team ['teamId'] 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams_champs WHERE teamId = \'{teamId}\'', array (
				'teamId' => ( int ) $team ['teamId'] 
		) );
	}

	/**
	 * Update an existing team (credits,transfersRemaining,scoreValue,modifiedDate) based on teamId
	 *
	 * @param array $team
	 */
	public function updateTeam(array $team) {
		$db = Application::getInstance ()->getDb ();
		$db->update ( '
			UPDATE dfl_teams SET 
				credits = \'{credits}\', 
				transfersRemaining = \'{transfersRemaining}\', 
				scoreValue = \'{scoreValue}\',
				modifiedDate = UTC_TIMESTAMP()  
				WHERE teamId = \'{teamId}\'', $team );
	}

	/**
	 * Update the champion order selection
	 *
	 * @param int $teamId
	 * @param array $champions
	 */
	public function updateChampionOrders($teamId, array $champions) {
		$db = Application::getInstance ()->getDb ();
		foreach ( $champions as $order => $id ) {
			$db->update ( '
					UPDATE dfl_team_champs SET displayOrder = \'{displayOrder}\'
					WHERE teamId = \'{teamId}\' AND championId = \'{championId}\'', array (
					'teamId' => $teamId,
					'championId' => $id,
					'displayOrder' => ($order + 1) 
			) );
		}
	}

	/**
	 * Update team credits,transfers
	 *
	 * @param array $team
	 */
	public function updateTeamResources(array $team) {
		$db = Application::getInstance ()->getDb ();
		$db->update ( '
			UPDATE dfl_teams SET 
				credits = \'{credits}\', 
				transfersRemaining = LEAST({transfersRemaining},{maxTransfers}), 
				modifiedDate = UTC_TIMESTAMP()  
			WHERE teamId = \'{teamId}\'', array (
				'maxTransfers' => Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'],
				'transfersRemaining' => $team ['transfersRemaining'],
				'credits' => $team ['credits'],
				'teamId' => $team ['teamId'] 
		) );
	}

	/**
	 * Transfer a champion out of a team
	 *
	 * @param array $team
	 * @param array $champ
	 */
	public function transferOut(array $team, array $champ) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
				INSERT INTO dfl_team_transfers SET 
				teamId = \'{teamId}\',
				championId = \'{championId}\',
				championValue = \'{championValue}\',
				transferType = \'OUT\',
				createdDate = UTC_TIMESTAMP()', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'championValue' => $champ ['championValue'] 
		) );
		$db->query ( 'DELETE FROM dfl_team_champs WHERE teamId = \'{teamId}\' AND championId = \'{championId}\'', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'] 
		) );
	}

	/**
	 * Transfer a champion into a team
	 *
	 * @param array $team
	 * @param array $champ
	 */
	public function transferIn(array $team, array $champ) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
				INSERT INTO dfl_team_transfers SET 
				teamId = \'{teamId}\',
				championId = \'{championId}\',
				championValue = \'{championValue}\',
				transferType = \'IN\',
				createdDate = UTC_TIMESTAMP()', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'championValue' => $champ ['championValue'] 
		) );
		$db->insert ( '
				INSERT INTO dfl_team_champs SET 
				teamId = \'{teamId}\',
				championId = \'{championId}\',
				displayOrder = 0,
				createdDate = UTC_TIMESTAMP()', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'championValue' => $champ ['championValue'] 
		) );
	}

}
