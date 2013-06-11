<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Application;
use Destiny\Service;
use Destiny\Config;
use Destiny\Service\Fantasy\Db\Game;
use Destiny\Utils\Cache;

class Aggregate extends Service {
	
	/**
	 *
	 * @var Destiny\Service\Fantasy\Db\Aggregate
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Destiny\Service\Fantasy\Db\Aggregate
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	public function aggregateGame($gameId) {
		$fgService = Game::getInstance ();
		$scores = Config::$a ['fantasy'] ['scores'];
		
		$game = $fgService->getGameById ( $gameId );
		if ($game ['aggregated'] == 1) {
			throw new \Exception ( 'Game already aggregated.' );
		}
		
		// Set the aggregated flag before an error occurs later on
		$this->setGameAggregated ( $gameId );
		
		// Get points for simply registering and being part of the game.
		$this->addTeamScore ( $gameId, 'PARTICIPATE', $scores ['PARTICIPATE'] );
		
		// Champion Context
		$this->addChampionScores ( $gameId, 'WIN', $scores ['WIN'], $game ['gameWinSideId'] );
		$this->addChampionScores ( $gameId, 'LOSE', $scores ['LOSE'], $game ['gameLoseSideId'] );
		
		$this->calculateChampionTeamScore ( $gameId, 'GAME' );
		
		// Recalc team scores, ranks, credits and milestones
		$this->calculateTeamScore ( $gameId );
		$this->calculateTeamRanks ();
		$this->calculateTeamCredits ( $gameId );
		$this->calculateMilestones ();
		$this->updateChampionStats ();
		return true;
	}
	
	// Note: negative earn rate is disregarded
	// This is dangerous, as the you cannot roll back "additions" like this.
	private function calculateTeamCredits($gameId) {
		$db = Application::getInstance ()->getDb ();
		$db->query ( '
			UPDATE dfl_teams AS `teams`
			INNER JOIN (
				SELECT 
					scores.teamId, 
					SUM(scores.scoreValue)*' . Config::$a ['fantasy'] ['credit'] ['scoreToCreditEarnRate'] . ' AS `earn`
				FROM dfl_scores_teams AS `scores`
				WHERE scores.gameId = \'{gameId}\'
				GROUP BY scores.teamId
			) AS `scores` ON (scores.teamId = teams.teamId)
			SET teams.credits = teams.credits + scores.earn
			WHERE scores.earn > 0', array (
				'gameId' => $gameId 
		) );
	}

	private function calculateMilestones() {
		$db = Application::getInstance ()->getDb ();
		foreach ( Config::$a ['fantasy'] ['milestones'] as $milestone ) {
			switch ($milestone ['type']) {
				
				case 'GAMEPOINTS' :
					// Make sure everyone has the milestone, and its up to date.
					// Note we dont set the goalValue on the duplicate key, because it needs to update in another query
					$db->query ( '
						INSERT INTO dfl_team_milestones (`teamId`, `milestoneType`, `milestoneValue`, `milestoneGoal`, `createdDate`, `modifiedDate`) 
						SELECT dfl_teams.teamId, \'{milestoneType}\', dfl_teams.scoreValue, \'{goalValue}\', UTC_TIMESTAMP(), UTC_TIMESTAMP() 
						FROM dfl_teams 
						ON DUPLICATE KEY UPDATE modifiedDate=VALUES(modifiedDate), milestoneValue=VALUES(milestoneValue)', array (
							'milestoneType' => $milestone ['type'],
							'goalValue' => $milestone ['goalValue'] 
					) );
					// Update the milestone (value = currentValue, goal = currentValue + startGoal)
					// Give reward
					if ($milestone ['reward'] ['type'] == 'TRANSFER') {
						$db->update ( '
							UPDATE dfl_teams 
							INNER JOIN dfl_team_milestones AS `milestones` ON (milestones.teamId = dfl_teams.teamId) 
							SET 
								dfl_teams.transfersRemaining = LEAST(dfl_teams.transfersRemaining + {rewardValue}, {maxTransfers}), 
								dfl_teams.modifiedDate = UTC_TIMESTAMP(), 
								milestones.milestoneValue = dfl_teams.scoreValue, 
								milestones.milestoneGoal = milestones.milestoneGoal+{goalValue}, 
								milestones.modifiedDate = UTC_TIMESTAMP() 
							WHERE milestones.milestoneValue > milestones.milestoneGoal AND milestones.milestoneType = \'{milestoneType}\'', array (
								'milestoneType' => $milestone ['type'],
								'rewardValue' => $milestone ['reward'] ['value'],
								'goalValue' => $milestone ['goalValue'],
								'maxTransfers' => Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'] 
						) );
					} else {
						throw new \Exception ( 'Unsupported reward type' );
					}
					break;
				
				case 'GAMES' :
					// Make sure everyone has the milestone, and its up to date.
					$db->query ( '
						INSERT INTO dfl_team_milestones (`teamId`, `milestoneType`, `milestoneValue`, `milestoneGoal`, `createdDate`, `modifiedDate`) 
						SELECT dfl_teams.teamId, \'{milestoneType}\', COUNT(dfl_scores_teams.teamId), \'{goalValue}\', UTC_TIMESTAMP(), UTC_TIMESTAMP() 
						FROM dfl_teams 
							LEFT JOIN dfl_scores_teams ON (dfl_teams.teamId = dfl_scores_teams.teamId AND dfl_scores_teams.scoreType = \'PARTICIPATE\') 
							GROUP BY dfl_scores_teams.teamId 
						ON DUPLICATE KEY UPDATE modifiedDate=VALUES(modifiedDate), milestoneValue=VALUES(milestoneValue)', array (
							'milestoneType' => $milestone ['type'],
							'goalValue' => $milestone ['goalValue'] 
					) );
					// Update the milestone (value = currentValue, goal = currentValue + startGoal)
					// Give reward
					if ($milestone ['reward'] ['type'] == 'TRANSFER') {
						$db->update ( '
							UPDATE dfl_teams 
								INNER JOIN dfl_team_milestones AS `milestones` ON (milestones.teamId = dfl_teams.teamId) SET 
								dfl_teams.transfersRemaining = LEAST(dfl_teams.transfersRemaining + {rewardValue}, {maxTransfers}), 
								dfl_teams.modifiedDate = UTC_TIMESTAMP(), 
								milestones.milestoneGoal = milestones.milestoneGoal+{goalValue}, 
								milestones.modifiedDate = UTC_TIMESTAMP() 
							WHERE milestones.milestoneValue > milestones.milestoneGoal AND milestones.milestoneType = \'{milestoneType}\' ', array (
								'milestoneType' => $milestone ['type'],
								'rewardValue' => $milestone ['reward'] ['value'],
								'goalValue' => $milestone ['goalValue'],
								'maxTransfers' => Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'] 
						) );
					} else {
						throw new \Exception ( 'Unsupported reward type' );
					}
					break;
			}
		}
	}

	private function setGameAggregated($gameId) {
		$db = Application::getInstance ()->getDb ();
		$db->update ( '
			UPDATE `dfl_games` AS `games` SET 
				aggregated = \'1\', 
				aggregatedDate = UTC_TIMESTAMP() 
			WHERE games.gameId = \'{gameId}\'', array (
				'gameId' => $gameId 
		) );
	}

	private function addTeamScore($gameId, $scoreType, $scoreValue) {
		$db = Application::getInstance ()->getDb ();
		$db->update ( '
			INSERT INTO dfl_scores_teams (`gameId`, `teamId`, `scoreValue`, `scoreType`, `createdDate`) 
			SELECT 
				\'{gameId}\' AS `gameId`, 
				teams.teamId AS `teamId`, 
				\'{scoreValue}\' AS `scoreValue`, 
				\'{scoreType}\' AS `scoreType`, 
				UTC_TIMESTAMP() AS `createdDate` 
			FROM dfl_teams AS `teams` ', array (
				'gameId' => $gameId,
				'scoreValue' => $scoreValue,
				'scoreType' => $scoreType 
		) );
	}

	private function addChampionScores($gameId, $scoreType, $scoreValue, $teamSideId) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
			INSERT INTO dfl_scores_champs (`gameId`, `championId`, `championMultiplier`, `scoreType`, `scoreValue`, `createdDate`) 
			SELECT 
				\'{gameId}\' AS `gameId`, 
				champs.championId AS `championId`, 
				champs.championMultiplier AS `championMultiplier`, 
				\'{scoreType}\' AS `scoreType`, 
				ROUND({scoreValue}*champs.championMultiplier) AS `scoreValue`, 
				UTC_TIMESTAMP() AS `createdDate` 
			FROM dfl_champs as `champs` 
			INNER JOIN dfl_games_champs AS `gamechamps` ON (gamechamps.championId = champs.championId) 
			WHERE gamechamps.gameId = \'{gameId}\' AND gamechamps.teamSideId = \'{teamSideId}\' ', array (
				'gameId' => $gameId,
				'scoreType' => $scoreType,
				'scoreValue' => $scoreValue,
				'teamSideId' => $teamSideId 
		) );
	}

	private function calculateChampionTeamScore($gameId, $scoreType) {
		$db = Application::getInstance ()->getDb ();
		// Owned champions only SCORES_TEAMS_CHAMPS
		$db->insert ( '
			INSERT INTO dfl_scores_teams_champs (`gameId`, `teamId`, `championId`, `championMultiplier`, `penalty`, `scoreValue`, `createdDate`) 
				SELECT 
					champscores.gameId, 
					teamchamps.teamId, 
					champs.championId, 
					champs.championMultiplier, 
					\'0\' AS `penalty`,
				
					ROUND(champscores.scoreValue + (champscores.scoreValue * (
					
							SELECT ((COUNT(*)-1)/({maxPotentialChamps}-1))*{teammateBonusModifier} FROM dfl_team_champs AS `a`
							INNER JOIN dfl_teams AS `e` ON (e.teamId = a.teamId)
							INNER JOIN dfl_games AS `b`
							INNER JOIN dfl_games_champs AS `c` ON (c.gameId = b.gameId AND c.championId = a.championId)
							INNER JOIN dfl_champs AS `f` ON (f.championId = c.championId)
							LEFT JOIN dfl_users_champs AS `j` ON (j.championId = c.championId AND j.userId = e.userId)
							WHERE a.teamId = teamchamps.teamId AND b.gameId = games.gameId AND c.teamSideId = gameschamps.teamSideId
							AND (j.championId IS NOT NULL OR f.championFree = 1)
							' . ((false == Config::$a ['fantasy'] ['timeAwareAggregation']) ? '' : 'AND a.createdDate < games.gameCreatedDate') . '
					
					))) AS `scoreValue`,
				
					UTC_TIMESTAMP() AS `createdDate` 
				FROM dfl_scores_champs AS `champscores` 
				INNER JOIN dfl_games AS `games` ON (games.gameId = champscores.gameId) 
				INNER JOIN dfl_champs AS `champs` ON (champs.championId = champscores.championId)
				INNER JOIN dfl_games_champs AS `gameschamps` ON (gameschamps.championId = champscores.championId AND gameschamps.gameId = champscores.gameId) 
				INNER JOIN dfl_team_champs AS `teamchamps` ON (teamchamps.championId = champscores.championId 
					' . ((false == Config::$a ['fantasy'] ['timeAwareAggregation']) ? '' : 'AND teamchamps.createdDate < games.gameCreatedDate') . '
				)
				INNER JOIN dfl_teams AS `teams` ON (teams.teamId = teamchamps.teamId) 
				INNER JOIN dfl_users_champs AS `userchamps` ON (userchamps.championId = champscores.championId AND userchamps.userId = teams.userId) 
				WHERE champscores.gameId = \'{gameId}\'
			', array (
				'gameId' => $gameId,
				'scoreType' => $scoreType,
				'maxPotentialChamps' => Config::$a ['fantasy'] ['team'] ['maxPotentialChamps'],
				'teammateBonusModifier' => Config::$a ['fantasy'] ['team'] ['teammateBonusModifier'] 
		) );
		// Free champions only SCORES_TEAMS_CHAMPS
		$db->insert ( '
			INSERT INTO dfl_scores_teams_champs (`gameId`, `teamId`, `championId`, `championMultiplier`, `penalty`, `scoreValue`, `createdDate`) 
				SELECT 
					champscores.gameId, 
					teamchamps.teamId, 
					champs.championId, 
					champs.championMultiplier, 
					\'{penalty}\' AS `penalty`,
				
					ROUND((champscores.scoreValue*(1-{penalty})) + (champscores.scoreValue*(1-{penalty})) * (
					
							SELECT ((COUNT(*)-1)/({maxPotentialChamps}-1))*{teammateBonusModifier} FROM dfl_team_champs AS `a`
							INNER JOIN dfl_teams AS `e` ON (e.teamId = a.teamId)
							INNER JOIN dfl_games AS `b`
							INNER JOIN dfl_games_champs AS `c` ON (c.gameId = b.gameId AND c.championId = a.championId)
							INNER JOIN dfl_champs AS `f` ON (f.championId = c.championId)
							LEFT JOIN dfl_users_champs AS `j` ON (j.championId = c.championId AND j.userId = e.userId)
							WHERE a.teamId = teamchamps.teamId AND b.gameId = games.gameId AND c.teamSideId = gameschamps.teamSideId
							AND (j.championId IS NOT NULL OR f.championFree = 1)
							' . ((false == Config::$a ['fantasy'] ['timeAwareAggregation']) ? '' : 'AND a.createdDate < games.gameCreatedDate') . '
					
					)) AS `scoreValue`,
				
				
					UTC_TIMESTAMP() AS `createdDate` 
				FROM dfl_scores_champs AS `champscores` 
				INNER JOIN dfl_games AS `games` ON (games.gameId = champscores.gameId) 
				INNER JOIN dfl_champs AS `champs` ON (champs.championId = champscores.championId) 
				INNER JOIN dfl_games_champs AS `gameschamps` ON (gameschamps.championId = champscores.championId AND gameschamps.gameId = champscores.gameId) 
				INNER JOIN dfl_team_champs AS `teamchamps` ON (teamchamps.championId = champscores.championId 
					' . ((false == Config::$a ['fantasy'] ['timeAwareAggregation']) ? '' : 'AND teamchamps.createdDate < games.gameCreatedDate') . '
				)
				INNER JOIN dfl_teams AS `teams` ON (teams.teamId = teamchamps.teamId) 
				LEFT JOIN dfl_users_champs AS `userchamps` ON (userchamps.championId = champscores.championId AND userchamps.userId = teams.userId) 
				WHERE userchamps.championId IS NULL AND champscores.gameId = \'{gameId}\' AND champs.championFree = 1
			', array (
				'gameId' => $gameId,
				'scoreType' => $scoreType,
				'penalty' => Config::$a ['fantasy'] ['team'] ['freeMultiplierPenalty'],
				'maxPotentialChamps' => Config::$a ['fantasy'] ['team'] ['maxPotentialChamps'],
				'teammateBonusModifier' => Config::$a ['fantasy'] ['team'] ['teammateBonusModifier'] 
		) );
		
		// Total team scores SCORES_TEAMS
		$db->insert ( '
			INSERT INTO dfl_scores_teams (`gameId`, `teamId`, `scoreValue`, `scoreType`, `createdDate`) 
			SELECT 
				teamchampscores.gameId AS `gameId`, 
				teamchampscores.teamId AS `teamId`, 
				SUM(teamchampscores.scoreValue) AS `scoreValue`, 
				\'{scoreType}\' AS `scoreType`, 
				UTC_TIMESTAMP() AS `createdDate` 
				FROM dfl_scores_teams_champs AS `teamchampscores` 
			WHERE teamchampscores.gameId = \'{gameId}\' 
			GROUP BY teamchampscores.teamId, teamchampscores.gameId', array (
				'gameId' => $gameId,
				'scoreType' => $scoreType 
		) );
	}

	/**
	 * This has the potential to become very slow, since it does a full re-calc for all teams
	 * Need to think of a way to reduce it
	 */
	public function calculateTeamScore($gameId = null) {
		$db = Application::getInstance ()->getDb ();
		if ($gameId == null) {
			$db->update ( '
			UPDATE dfl_teams AS `teams`, ( 
				SELECT scoresteams.teamId, SUM(scoresteams.scoreValue) AS `total` 
				FROM dfl_scores_teams AS `scoresteams` 
				GROUP BY scoresteams.teamId 
			) AS scoresteams 
			SET teams.scoreValue = scoresteams.total, teams.modifiedDate = UTC_TIMESTAMP()
			WHERE teams.teamId = scoresteams.teamId' );
		} else {
			$db->update ( '
				UPDATE dfl_teams AS `teams`, ( 
					SELECT scoresteams.teamId, SUM(scoresteams.scoreValue) AS `total` 
					FROM dfl_scores_teams AS `scoresteams`
					WHERE scoresteams.gameId = \'{gameId}\' 
					GROUP BY scoresteams.teamId 
				) AS scoresteams 
				INNER JOIN dfl_scores_teams AS `scoresteams` ON (scoresteams.teamId = teams.teamId)
				SET teams.scoreValue = scoresteams.total, teams.modifiedDate = UTC_TIMESTAMP()
				WHERE scoresteams.gameId = \'{gameId}\'
			', array (
					'gameId' => $gameId 
			) );
		}
	}

	public function calculateTeamRanks() {
		$db = Application::getInstance ()->getDb ();
		$db->query ( '
			INSERT IGNORE INTO dfl_team_ranks ( 
				SELECT 
					teams.teamId, 
					0 AS `rank` 
				FROM dfl_teams AS `teams`
			)' );
		$db->query ( 'SET @rank=0' );
		$db->update ( '
			UPDATE dfl_team_ranks AS `ranks`, ( 
				SELECT 
				@rank:=@rank+1 AS `teamRank`, 
				teams.teamId, 
				teams.scoreValue 
				FROM dfl_teams AS `teams` 
				ORDER BY teams.scoreValue DESC 
			) AS `teamranks` 
			SET ranks.teamRank = teamranks.teamRank 
			WHERE ranks.teamId = teamranks.teamId' );
	}

	public function resetGame($gameId) {
		$db = Application::getInstance ()->getDb ();
		$db->query ( 'DELETE FROM dfl_scores_champs WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams_champs WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'UPDATE dfl_games SET aggregated=0 WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$this->calculateTeamScore ();
		$this->calculateTeamRanks ();
	}

	public function removeGame($gameId) {
		$db = Application::getInstance ()->getDb ();
		$db->query ( 'DELETE FROM dfl_games WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_games_champs WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_scores_champs WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$db->query ( 'DELETE FROM dfl_scores_teams_champs WHERE gameId = \'{gameId}\'', array (
				'gameId' => ( int ) $gameId 
		) );
		$this->calculateTeamScore ();
		$this->calculateTeamRanks ();
	}

	/**
	 * Update champion
	 * gamesWin & gamesLost & gamesPlayed
	 */
	public function updateChampionStats() {
		$db = Application::getInstance ()->getDb ();
		$db->query ( '
		UPDATE dfl_champs AS `champs`
		INNER JOIN (
			SELECT _champs.championId,
			COALESCE((
				SELECT COUNT(chgames.championId) FROM dfl_games_champs `chgames`
				INNER JOIN dfl_games AS `_games` ON (_games.gameId = chgames.gameId)
				WHERE chgames.championId = _champs.championId AND chgames.teamSideId = _games.gameWinSideId
				GROUP BY championId
			), 0) `_gamesWin`,
			COALESCE((
				SELECT COUNT(chgames.championId) FROM dfl_games_champs `chgames`
				INNER JOIN dfl_games AS `_games` ON (_games.gameId = chgames.gameId)
				WHERE chgames.championId = _champs.championId AND chgames.teamSideId = _games.gameLoseSideId
				GROUP BY championId
			), 0) `_gamesLost`,
			COALESCE((
				SELECT COUNT(chgames.championId) FROM dfl_games_champs `chgames`
				INNER JOIN dfl_games AS `_games` ON (_games.gameId = chgames.gameId)
				WHERE chgames.championId = _champs.championId
				GROUP BY championId
			), 0) `_gamesPlayed`
			FROM dfl_champs `_champs`
		) AS `champstats` ON (champstats.championId = champs.championId)
		SET 
			champs.gamesWin = champstats._gamesWin, 
			champs.gamesLost = champstats._gamesLost,
			champs.gamesPlayed = champstats._gamesPlayed
		' );
		$db->query ( '
		UPDATE dfl_champs AS `champs`
		INNER JOIN (
			SELECT
				_champs.championId,
				(_champs.gamesPlayed/(
					SELECT MAX(gamesPlayed) FROM dfl_champs
				)) `playedRatio`,
				COALESCE((_champs.gamesWin/_champs.gamesPlayed),0) `WinRatio`,
				COALESCE((_champs.gamesLost/_champs.gamesPlayed),0) `LossRatio`
			FROM dfl_champs AS `_champs`
		) AS `champstats` ON (champstats.championId = champs.championId)
		SET champs.championMultiplier = ROUND(1-champstats.playedRatio*champstats.WinRatio, 3)
		' );
	}

	public function createTeamsSnapshot($gameId, $date) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
			INSERT INTO dfl_team_champs_snapshot (`teamId`,`championId`,`gameId`,`createdDate`) 
			SELECT 
				teamchamps.teamId,
				teamchamps.championId,
				\'{gameId}\' AS `gameId`,
				NOW() AS `createdDate`
			FROM dfl_team_champs AS `teamchamps`
			INNER JOIN dfl_teams AS `teams` ON (teams.teamId = teamchamps.teamId)
			WHERE teamchamps.createdDate < \'{date}\'
		', array (
				'gameId' => $gameId,
				'date' => $date 
		) );
	}

}
