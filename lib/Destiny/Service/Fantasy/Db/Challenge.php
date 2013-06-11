<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;

class Challenge extends Service {
	
	/**
	 *
	 * @var Service
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Destiny\Service\Fantasy\Db\Challenge
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function getTeamChallengers($teamId, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
				SELECT 
					users.displayName,
					users.country,
					IF(subs.userId IS NULL,0,1) AS `subscriber`,
					teams.teamId,
					teams.credits,
					teams.scoreValue,
					teams.transfersRemaining,
					teams.teamActive,
					teams.createdDate,
					( 
						SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',{maxChampions}))
						FROM dfl_team_champs AS `champs`  
						WHERE champs.teamId = challengers.challengeTeamId
					) AS `champions` 
				FROM dfl_teams AS `teams`
				LEFT JOIN dfl_challengers AS `challengers` ON (challengers.challengeTeamId = teams.teamId)
				INNER JOIN dfl_users AS `users` ON (users.userId = challengers.challengeTeamId)
				LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
				LEFT JOIN dfl_team_ranks AS `ranks` ON (ranks.teamId = challengers.challengeTeamId)  
				WHERE ((challengers.ownerTeamId = {teamId} AND challengers.status = \'ACCEPTED\') OR teams.teamId = {teamId}) AND teams.teamActive = 1
				GROUP BY teams.teamId
				ORDER BY teams.scoreValue DESC, ranks.teamRank ASC
				LIMIT {offset},{limit}
				', array (
				'offset' => $offset,
				'limit' => $limit,
				'teamId' => $teamId,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getInvites($teamId, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT 
				users.displayName,
				users.country,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				teams.*, 
				( 
					SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',{maxChampions}))
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = challengers.challengeTeamId
				) AS `champions` 
			FROM dfl_challengers AS `challengers`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = challengers.ownerTeamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId)
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
			WHERE challengers.challengeTeamId = {teamId} AND challengers.status = \'SENT\'
			ORDER BY challengers.createdDate DESC
			', array (
				'teamId' => $teamId,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getSentInvites($teamId, $limit = 1, $offset = 0) {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT 
				users.displayName,
				users.country,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				teams.*, 
				( 
					SELECT COALESCE(SUBSTRING_INDEX(GROUP_CONCAT(champs.championId ORDER BY champs.displayOrder ASC),\',\',{maxChampions}))
					FROM dfl_team_champs AS `champs`  
					WHERE champs.teamId = challengers.challengeTeamId
				) AS `champions` 
			FROM dfl_challengers AS `challengers`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = challengers.challengeTeamId)
			INNER JOIN dfl_users AS `users` ON (users.userId = teams.userId)
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = teams.userId AND subs.endDate > NOW() AND subs.status = \'Active\') 
			WHERE challengers.ownerTeamId = {teamId} AND challengers.status = \'SENT\'
			ORDER BY challengers.createdDate DESC
			', array (
				'teamId' => $teamId,
				'maxChampions' => Config::$a ['fantasy'] ['team'] ['maxChampions'] 
		) )->fetchRows ();
	}

	public function getChallengeExists($ownerTeamId, $teamId) {
		$db = Application::getInstance ()->getDb ();
		return (intval ( $db->select ( '
				SELECT 
					COUNT(ownerTeamId) AS `challengeCount` 
				FROM dfl_challengers WHERE ownerTeamId = \'{ownerTeamId}\' AND challengeTeamId = \'{challengeTeamId}\'', array (
				'ownerTeamId' => $ownerTeamId,
				'challengeTeamId' => $teamId 
		) )->fetchValue ( 'challengeCount' ) ) > 0) ? true : false;
	}

	public function challengeTeam($ownerTeamId, $teamId) {
		$db = Application::getInstance ()->getDb ();
		if (! $this->getChallengeExists ( $ownerTeamId, $teamId )) {
			$db->insert ( '
				INSERT INTO dfl_challengers SET 
					ownerTeamId = \'{ownerTeamId}\', 
					challengeTeamId = \'{challengeTeamId}\', 
					status = \'SENT\', 
					createdDate = NOW()
				', array (
					'ownerTeamId' => $ownerTeamId,
					'challengeTeamId' => $teamId 
			) );
			return true;
		}
		return false;
	}

	public function acceptChallenge($challengerId, $teamId) {
		$db = Application::getInstance ()->getDb ();
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$db->update ( '
				UPDATE dfl_challengers SET 
					status = \'ACCEPTED\' 
				WHERE ownerTeamId = \'{ownerTeamId}\' AND challengeTeamId = \'{challengeTeamId}\'
				', array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			) );
			// Automatically add the team to the person group that accepted the invite
			if (! $this->getChallengeExists ( $teamId, $challengerId )) {
				$db->insert ( '
					INSERT INTO dfl_challengers SET 
						ownerTeamId = \'{ownerTeamId}\', 
						challengeTeamId = \'{challengeTeamId}\', 
						status = \'ACCEPTED\', 
						createdDate = NOW()
					', array (
						'ownerTeamId' => $teamId,
						'challengeTeamId' => $challengerId 
				) );
			} else {
				$db->insert ( '
					UPDATE dfl_challengers SET 
						status = \'ACCEPTED\' 
					WHERE ownerTeamId = \'{ownerTeamId}\' AND challengeTeamId = \'{challengeTeamId}\'
					', array (
						'ownerTeamId' => $teamId,
						'challengeTeamId' => $challengerId 
				) );
			}
			//
			return true;
		}
		return false;
	}

	public function declineChallenge($challengerId, $teamId) {
		$db = Application::getInstance ()->getDb ();
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$db->update ( '
				UPDATE dfl_challengers SET 
					status = \'DECLINED\' 
				WHERE ownerTeamId = \'{ownerTeamId}\' AND challengeTeamId = \'{challengeTeamId}\'
				', array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			) );
			return true;
		}
		return false;
	}

	public function deleteChallenge($challengerId, $teamId) {
		$db = Application::getInstance ()->getDb ();
		if ($this->getChallengeExists ( $challengerId, $teamId )) {
			$db->query ( '
				DELETE FROM dfl_challengers 
				WHERE (ownerTeamId = \'{ownerTeamId}\' AND challengeTeamId = \'{challengeTeamId}\') OR (ownerTeamId = \'{challengeTeamId}\' AND challengeTeamId = \'{ownerTeamId}\')
				', array (
					'ownerTeamId' => $challengerId,
					'challengeTeamId' => $teamId 
			) );
			return true;
		}
		return false;
	}

}