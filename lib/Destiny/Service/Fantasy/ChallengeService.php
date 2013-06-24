<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\Utils\Cache;

class ChallengeService extends Service {
	
	/**
	 *
	 * @var ChallengeService
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Destiny\Service\Fantasy\ChallengeService
	 */
	public static function instance() {
		return parent::instance ();
	}

	public function getTeamChallengers($teamId, $limit = 1, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.username,
				users.country,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				teams.teamId,
				teams.credits,
				teams.scoreValue,
				teams.transfersRemaining,
				teams.createdDate,
				( 
					SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions))
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = challengers.challengeTeamId
				) AS `champions` 
			FROM dfl_teams AS `teams`
			LEFT JOIN dfl_challengers AS `challengers` ON (challengers.challengeTeamId = teams.teamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = challengers.challengeTeamId AND users.userStatus = \'Active\')
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
			LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = challengers.challengeTeamId)  
			WHERE ((challengers.ownerTeamId = :teamId AND challengers.status = \'ACCEPTED\') OR teams.teamId = :teamId)
			GROUP BY teams.teamId
			ORDER BY teams.scoreValue DESC, ranks.teamRank ASC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getInvites($teamId, $limit = 50, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.username,
				users.country,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				teams.*, 
				( 
					SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions))
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = challengers.challengeTeamId
				) AS `champions` 
			FROM dfl_challengers AS `challengers`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = challengers.ownerTeamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\')
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			WHERE challengers.challengeTeamId = :teamId AND challengers.status = \'SENT\'
			ORDER BY challengers.createdDate DESC
			LIMIT :offset,:limit	
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getSentInvites($teamId, $limit = 50, $offset = 0) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT 
				users.username,
				users.country,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				teams.*, 
				( 
					SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',:maxChampions))
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = challengers.challengeTeamId
				) AS `champions` 
			FROM dfl_challengers AS `challengers`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = challengers.challengeTeamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId AND users.userStatus = \'Active\')
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.status = \'Active\') 
			WHERE challengers.ownerTeamId = :teamId AND challengers.status = \'SENT\'
			ORDER BY challengers.createdDate DESC
			LIMIT :offset,:limit
		' );
		$stmt->bindValue ( 'offset', $offset, \PDO::PARAM_INT );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxChampions', Config::$a ['fantasy'] ['team'] ['maxChampions'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'teamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return $stmt->fetchAll ();
	}

	public function getChallengeExists($ownerTeamId, $teamId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT COUNT(ownerTeamId) AS `challengeCount` FROM dfl_challengers 
			WHERE ownerTeamId = :ownerTeamId AND challengeTeamId = :challengeTeamId		
		' );
		$stmt->bindValue ( 'ownerTeamId', $ownerTeamId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'challengeTeamId', $teamId, \PDO::PARAM_INT );
		$stmt->execute ();
		return (intval ( $stmt->fetchColumn () ) > 0);
	}

	public function challengeTeam($ownerTeamId, $teamId) {
		if (! $this->getChallengeExists ( $ownerTeamId, $teamId )) {
			$conn = Application::instance ()->getConnection ();
			$conn->insert ( 'dfl_challengers', array (
					'ownerTeamId' => $ownerTeamId,
					'challengeTeamId' => $teamId,
					'status' => 'SENT',
					'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
			), array (
					\PDO::PARAM_INT,
					\PDO::PARAM_INT,
					\PDO::PARAM_STR,
					\PDO::PARAM_STR 
			) );
			return true;
		}
		return false;
	}

	public function acceptChallenge($challengerId, $teamId) {
		$conn = Application::instance ()->getConnection ();
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$conn->update ( 'dfl_challengers', array (
					'status' => 'ACCEPTED' 
			), array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			), array (
					\PDO::PARAM_STR,
					\PDO::PARAM_INT,
					\PDO::PARAM_INT 
			) );
			// Automatically add the team to the person group that accepted the invite
			if (! $this->getChallengeExists ( $teamId, $challengerId )) {
				$conn->insert ( 'dfl_challengers', array (
						'ownerTeamId' => $teamId,
						'challengeTeamId' => $challengerId,
						'status' => 'ACCEPTED',
						'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
				), array (
						\PDO::PARAM_INT,
						\PDO::PARAM_INT,
						\PDO::PARAM_STR,
						\PDO::PARAM_STR 
				) );
			} else {
				$conn->update ( 'dfl_challengers', array (
						'status' => 'ACCEPTED' 
				), array (
						'ownerTeamId' => $teamId,
						'challengeTeamId' => $challengerId 
				), array (
						\PDO::PARAM_STR,
						\PDO::PARAM_INT,
						\PDO::PARAM_INT 
				) );
			}
			return true;
		}
		return false;
	}

	public function declineChallenge($challengerId, $teamId) {
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$conn = Application::instance ()->getConnection ();
			$conn->update ( 'dfl_challengers', array (
					'status' => 'DECLINED' 
			), array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			), array (
					\PDO::PARAM_STR,
					\PDO::PARAM_INT,
					\PDO::PARAM_INT 
			) );
			return true;
		}
		return false;
	}

	public function deleteChallenge($challengerId, $teamId) {
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$conn = Application::instance ()->getConnection ();
			$conn->delete ( 'dfl_challengers', array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			) );
			$conn->delete ( 'dfl_challengers', array (
					'ownerTeamId' => $teamId,
					'challengeTeamId' => $challengerId 
			) );
			return true;
		}
		return false;
	}

}