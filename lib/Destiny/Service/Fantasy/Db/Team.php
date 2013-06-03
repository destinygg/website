<?php
namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;

class Team extends Service {
	
	protected static $instance = null;

	/**
	 *
	 * @return Service\Fantasy\Db\Team
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function setupUserTeam(array $user) {
		$teams = $this->getTeamsByUserId ( ( int ) $user ['userId'] );
		if (count ( $teams ) <= 0) {
			return $this->setupNewTeam ( $user );
		}
		return null;
	}

	public function setupNewTeam(array $user) {
		$db = Application::getInstance ()->getDb ();
		$team = array (
				'teamId' => null,
				'userId' => $user ['userId'],
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

	public function getTeamByUserId($userId) {
		$teams = self::getTeamsByUserId ( $userId );
		return (isset ( $teams [0] )) ? $teams [0] : null;
	}

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
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.active = 1) 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = teams.teamId) 
				ORDER BY CASE WHEN ranks.teamRank IS NULL THEN 1 ELSE 0 end, ranks.teamRank ASC, users.username DESC 
				LIMIT {offset},{limit} ', array (
				'offset' => $offset,
				'limit' => $limit 
		) )->fetchRows ();
	}

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
				'credits' => $team ['credits'] ,
				'teamId' => $team ['teamId'] 
		) );
	}

	public function insertTeam(array $team) {
		$db = Application::getInstance ()->getDb ();
		$id = $db->insert ( '
				INSERT INTO dfl_teams SET 
					userId = \'{userId}\',
					credits = \'{credits}\',
					scoreValue = \'{scoreValue}\',
					transfersRemaining = LEAST({transfersRemaining},{maxTransfers}), 
					createdDate =  UTC_TIMESTAMP(),
					modifiedDate = UTC_TIMESTAMP()', array (
				'userId' => $team ['userId'],
				'credits' => $team ['credits'],
				'scoreValue' => $team ['scoreValue'],
				'transfersRemaining' => $team ['transfersRemaining'],
				'maxTransfers' => Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'] 
		) );
		$team ['teamId'] = $id;
		return $team;
	}

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
