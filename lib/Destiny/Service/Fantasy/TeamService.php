<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Utils\Date;

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
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Setup a new team for a user
	 *
	 * @param nit $userId
	 * @return array
	 */
	public function addTeam($userId, $credits, $transfers) {
		$team = array (
				'userId' => $userId,
				'credits' => Config::$a ['fantasy'] ['team'] ['startCredit'],
				'transfersRemaining' => Config::$a ['fantasy'] ['team'] ['startTransfers'],
				'scoreValue' => 0,
				'teamActive' => true,
				'modifiedDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ),
				'createdDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		);
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_teams', $team );
		return $conn->lastInsertId ();
	}

	/**
	 * Get a team record by teamId
	 *
	 * @param string $teamId
	 */
	public function getTeamById($teamId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT * FROM dfl_teams AS `teams` WHERE teams.teamId = :teamId LIMIT 0,1' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetch ();
	}

	/**
	 * Return a list of teams by the teams username
	 *
	 * @param string $username
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeamsByUsername($username, $limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				teams.*, 
				users.username, 
				ranks.teamRank 
			FROM dfl_teams AS `teams` 
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
			WHERE users.username = :username 
			ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
			LIMIT :offset,:limit 
		' );
		$stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
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
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT teams.*, users.username, ranks.teamRank 
			FROM dfl_teams AS `teams` 
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
			WHERE teams.userId = :userId 
			ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get the teams transfers
	 *
	 * @param int $teamId
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeamTransfers($teamId, $limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT * FROM dfl_team_transfers AS `transfers` 
			INNER JOIN dfl_champs AS `champs` ON (champs.championId = transfers.championId) 
			WHERE transfers.teamId = :teamId 
			ORDER BY transfers.createdDate DESC, FIELD(transferType, \'OUT\', \'IN\') 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Get a flat list of teams
	 *
	 * @param int $limit
	 * @param int $offset
	 */
	public function getTeams($limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				teams.*, 
				users.userId, 
				users.username, 
				users.displayName, 
				IF(subs.userId IS NULL,0,1) AS `subscriber` 
			FROM dfl_teams AS `teams` 
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId) 
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
			ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
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
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT teamchamps.*, champs.*, userchamps.championId IS NOT NULL AS `unlocked` FROM dfl_team_champs AS `teamchamps` 
			INNER JOIN `dfl_teams` AS `teams` ON (teams.teamId = teamchamps.teamId) 
			INNER JOIN `dfl_champs` AS `champs` ON (teamchamps.championId = champs.championId) 
			LEFT JOIN `dfl_users_champs` AS `userchamps` ON (userchamps.userId = teams.userId AND userchamps.championId = champs.championId) 
			WHERE teams.teamId = :teamId
			ORDER BY teamchamps.displayOrder, champs.championName DESC, teamchamps.createdDate 
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	/**
	 * Reset and remove team
	 *
	 * @param array $team
	 */
	public function resetTeam(array $team) {
		// Update the team stats
		$team ['credits'] = Config::$a ['fantasy'] ['team'] ['startCredit'];
		$team ['transfersRemaining'] = Config::$a ['fantasy'] ['team'] ['startTransfers'];
		$team ['scoreValue'] = 0;
		$this->updateTeam ( $team );
		
		// Clear the team ranks, champs etc
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_team_ranks', array (
				'teamId' => $team ['teamId'] 
		) );
		$conn->delete ( 'dfl_users_champs', array (
				'userId' => $team ['userId'] 
		) );
		$conn->delete ( 'dfl_team_champs', array (
				'teamId' => $team ['teamId'] 
		) );
		$conn->delete ( 'dfl_team_milestones', array (
				'teamId' => $team ['teamId'] 
		) );
		$conn->delete ( 'dfl_scores_teams', array (
				'teamId' => $team ['teamId'] 
		) );
		$conn->delete ( 'dfl_scores_teams_champs', array (
				'teamId' => $team ['teamId'] 
		) );
	}

	/**
	 * Update an existing team (credits,transfersRemaining,scoreValue,modifiedDate) based on teamId
	 *
	 * @param array $team
	 */
	public function updateTeam(array $team) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_teams', array (
				'credits' => $team ['credits'],
				'transfersRemaining' => $team ['transfersRemaining'],
				'scoreValue' => $team ['scoreValue'],
				'modifiedDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		), array (
				'teamId' => $team ['teamId'] 
		) );
	}

	/**
	 * Update the champion order selection
	 *
	 * @param int $teamId
	 * @param array $champions
	 */
	public function updateChampionOrders($teamId, array $champions) {
		$conn = Application::instance ()->getConnection ();
		foreach ( $champions as $order => $id ) {
			$conn->update ( 'dfl_team_champs', array (
					'displayOrder' => ($order + 1) 
			), array (
					'teamId' => $teamId,
					'championId' => $id 
			) );
		}
	}

	/**
	 * Update team credits,transfers
	 *
	 * @param array $team
	 */
	public function updateTeamResources(array $team) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			UPDATE dfl_teams SET 
				credits = :credits, 
				transfersRemaining = LEAST(:transfersRemaining,:maxTransfers), 
				modifiedDate = UTC_TIMESTAMP()  
			WHERE teamId = :teamId
		' );
		$stmt->bindValue ( 'credits', $team ['credits'], \PDO::PARAM_STR );
		$stmt->bindValue ( 'teamId', $team ['teamId'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'transfersRemaining', $team ['transfersRemaining'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxTransfers', Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'], \PDO::PARAM_INT );
		$stmt->execute ();
	}

	/**
	 * Transfer a champion out of a team
	 *
	 * @param array $team
	 * @param array $champ
	 */
	public function transferOut(array $team, array $champ) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_team_transfers', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'championValue' => $champ ['championValue'],
				'transferType' => 'OUT',
				'createdDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		) );
		$conn->delete ( 'dfl_team_champs', array (
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
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_team_transfers', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'championValue' => $champ ['championValue'],
				'transferType' => 'IN',
				'createdDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		) );
		$conn->insert ( 'dfl_team_champs', array (
				'teamId' => $team ['teamId'],
				'championId' => $champ ['championId'],
				'displayOrder' => 0,
				'createdDate' => Date::getDateTime ( time (), 'Y-m-d H:i:s' ) 
		) );
	}

}
