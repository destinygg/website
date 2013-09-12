<?php
namespace Destiny\Common\Service\Fantasy;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\Service\Fantasy\GameService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Exception;

class GameAggregationService extends Service {
	
	protected static $instance = null;

	/**
	 * Singleton instance
	 *
	 * @return GameAggregationService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * Aggregate / calculate the scoring for a game
	 *
	 * @param int $gameId
	 * @throws Exception
	 * @return boolean
	 */
	public function aggregateGame($gameId) {
		$fgService = GameService::instance ();
		$scores = Config::$a ['fantasy'] ['scores'];
		
		$game = $fgService->getGameById ( $gameId );
		if (empty ( $game )) {
			throw new Exception ( 'Game data not found' );
		}
		if ($game ['aggregated'] == 1) {
			throw new Exception ( 'Game already aggregated.' );
		}
		
		// Set the aggregated flag before an error occurs later on
		$this->setGameAggregated ( $gameId );
		
		// Get points for simply registering and being part of the game.
		$this->addTeamScore ( $gameId, 'PARTICIPATE', $scores ['PARTICIPATE'] );
		
		// Champion Context
		$this->addChampionScores ( $gameId, 'WIN', $scores ['WIN'], $game ['gameWinSideId'] );
		$this->addChampionScores ( $gameId, 'LOSE', $scores ['LOSE'], $game ['gameLoseSideId'] );
		
		$this->calculateChampionTeamScore ( $gameId );
		
		// Recalc team scores, ranks, credits and milestones
		$this->calculateTeamScore ( $gameId );
		$this->calculateTeamRanks ();
		$this->calculateTeamCredits ( $gameId );
		$this->calculateMilestones ();
		$this->updateChampionStats ();
		return true;
	}
	
	// Note: negative earn rate is disregarded
	// This is dangerous you cannot roll back "additions" like this.
	private function calculateTeamCredits($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			UPDATE dfl_teams AS `teams`
			INNER JOIN (
				SELECT 
					scores.teamId, 
					SUM(scores.scoreValue)*' . Config::$a ['fantasy'] ['credit'] ['scoreToCreditEarnRate'] . ' AS `earn`
				FROM dfl_scores_teams AS `scores`
				WHERE scores.gameId = :gameId
				GROUP BY scores.teamId
			) AS `scores` ON (scores.teamId = teams.teamId)
			SET teams.credits = teams.credits + scores.earn
			WHERE scores.earn > 0
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
	}

	private function calculateMilestones() {
		$conn = Application::instance ()->getConnection ();
		foreach ( Config::$a ['fantasy'] ['milestones'] as $milestone ) {
			switch ($milestone ['type']) {
				
				case 'GAMEPOINTS' :
					// Make sure everyone has the milestone, and its up to date.
					// Note we dont set the goalValue on the duplicate key, because it needs to update in another query
					$stmt = $conn->prepare ( '
						INSERT INTO dfl_team_milestones (`teamId`, `milestoneType`, `milestoneValue`, `milestoneGoal`, `createdDate`, `modifiedDate`) 
						SELECT dfl_teams.teamId, :milestoneType, dfl_teams.scoreValue, :goalValue, UTC_TIMESTAMP(), UTC_TIMESTAMP() 
						FROM dfl_teams 
						ON DUPLICATE KEY UPDATE modifiedDate=VALUES(modifiedDate), milestoneValue=VALUES(milestoneValue)
					' );
					$stmt->bindValue ( 'milestoneType', $milestone ['type'], \PDO::PARAM_STR );
					$stmt->bindValue ( 'goalValue', $milestone ['goalValue'], \PDO::PARAM_INT );
					$stmt->execute ();
					
					// Update the milestone (value = currentValue, goal = currentValue + startGoal)
					// Give reward
					if ($milestone ['reward'] ['type'] == 'TRANSFER') {
						$stmt = $conn->prepare ( '
							UPDATE dfl_teams 
							INNER JOIN dfl_team_milestones AS `milestones` ON (milestones.teamId = dfl_teams.teamId) 
							SET 
								dfl_teams.transfersRemaining = LEAST(dfl_teams.transfersRemaining + :rewardValue, :maxTransfers), 
								dfl_teams.modifiedDate = UTC_TIMESTAMP(), 
								milestones.milestoneValue = dfl_teams.scoreValue, 
								milestones.milestoneGoal = milestones.milestoneGoal+:goalValue, 
								milestones.modifiedDate = UTC_TIMESTAMP() 
							WHERE milestones.milestoneValue > milestones.milestoneGoal AND milestones.milestoneType = :milestoneType
						' );
						$stmt->bindValue ( 'milestoneType', $milestone ['type'], \PDO::PARAM_STR );
						$stmt->bindValue ( 'rewardValue', $milestone ['reward'] ['value'], \PDO::PARAM_INT );
						$stmt->bindValue ( 'goalValue', $milestone ['goalValue'], \PDO::PARAM_INT );
						$stmt->bindValue ( 'maxTransfers', Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'], \PDO::PARAM_INT );
						$stmt->execute ();
					} else {
						throw new Exception ( 'Unsupported reward type' );
					}
					break;
				
				case 'GAMES' :
					// Make sure everyone has the milestone, and its up to date.
					$stmt = $conn->prepare ( '
						INSERT INTO dfl_team_milestones (`teamId`, `milestoneType`, `milestoneValue`, `milestoneGoal`, `createdDate`, `modifiedDate`) 
						SELECT dfl_teams.teamId, :milestoneType, COUNT(dfl_scores_teams.teamId), :goalValue, UTC_TIMESTAMP(), UTC_TIMESTAMP() 
						FROM dfl_teams 
							LEFT JOIN dfl_scores_teams ON (dfl_teams.teamId = dfl_scores_teams.teamId AND dfl_scores_teams.scoreType = \'PARTICIPATE\') 
							GROUP BY dfl_scores_teams.teamId 
						ON DUPLICATE KEY UPDATE modifiedDate=VALUES(modifiedDate), milestoneValue=VALUES(milestoneValue)
					' );
					$stmt->bindValue ( 'milestoneType', $milestone ['type'], \PDO::PARAM_STR );
					$stmt->bindValue ( 'goalValue', $milestone ['goalValue'], \PDO::PARAM_INT );
					$stmt->execute ();
					// Update the milestone (value = currentValue, goal = currentValue + startGoal)
					// Give reward
					if ($milestone ['reward'] ['type'] == 'TRANSFER') {
						$stmt = $conn->prepare ( '
							UPDATE dfl_teams 
								INNER JOIN dfl_team_milestones AS `milestones` ON (milestones.teamId = dfl_teams.teamId) SET 
								dfl_teams.transfersRemaining = LEAST(dfl_teams.transfersRemaining + :rewardValue, :maxTransfers), 
								dfl_teams.modifiedDate = UTC_TIMESTAMP(), 
								milestones.milestoneGoal = milestones.milestoneGoal+:goalValue, 
								milestones.modifiedDate = UTC_TIMESTAMP() 
							WHERE milestones.milestoneValue > milestones.milestoneGoal AND milestones.milestoneType = :milestoneType 
						' );
						$stmt->bindValue ( 'milestoneType', $milestone ['type'], \PDO::PARAM_STR );
						$stmt->bindValue ( 'rewardValue', $milestone ['reward'] ['value'], \PDO::PARAM_INT );
						$stmt->bindValue ( 'goalValue', $milestone ['goalValue'], \PDO::PARAM_INT );
						$stmt->bindValue ( 'maxTransfers', Config::$a ['fantasy'] ['team'] ['maxAvailableTransfers'], \PDO::PARAM_INT );
						$stmt->execute ();
					} else {
						throw new Exception ( 'Unsupported reward type' );
					}
					break;
			}
		}
	}

	private function setGameAggregated($gameId) {
		$conn = Application::instance ()->getConnection ();
		$conn->update ( 'dfl_games', array (
			'aggregated' => true,
			'aggregatedDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		), array (
			'gameId' => $gameId 
		), array (
			\PDO::PARAM_BOOL,
			\PDO::PARAM_STR,
			\PDO::PARAM_INT 
		) );
	}

	private function addTeamScore($gameId, $scoreType, $scoreValue) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			INSERT INTO dfl_scores_teams (`gameId`, `teamId`, `scoreValue`, `scoreType`, `createdDate`) 
			SELECT 
				:gameId AS `gameId`, 
				teams.teamId AS `teamId`, 
				:scoreValue AS `scoreValue`, 
				:scoreType AS `scoreType`, 
				UTC_TIMESTAMP() AS `createdDate` 
			FROM dfl_teams AS `teams`		
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'scoreType', $scoreType, \PDO::PARAM_STR );
		$stmt->bindValue ( 'scoreValue', $scoreValue, \PDO::PARAM_INT );
		$stmt->execute ();
	}

	private function addChampionScores($gameId, $scoreType, $scoreValue, $teamSideId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			INSERT INTO dfl_scores_champs (`gameId`, `championId`, `championMultiplier`, `scoreType`, `scoreValue`, `createdDate`) 
			SELECT 
				:gameId AS `gameId`, 
				champs.championId AS `championId`, 
				champs.championMultiplier AS `championMultiplier`, 
				:scoreType AS `scoreType`, 
				ROUND(:scoreValue*champs.championMultiplier) AS `scoreValue`, 
				UTC_TIMESTAMP() AS `createdDate` 
			FROM dfl_champs as `champs` 
			INNER JOIN dfl_games_champs AS `gamechamps` ON (gamechamps.championId = champs.championId) 
			WHERE gamechamps.gameId = :gameId AND gamechamps.teamSideId = :teamSideId 
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'scoreType', $scoreType, \PDO::PARAM_STR );
		$stmt->bindValue ( 'scoreValue', $scoreValue, \PDO::PARAM_INT );
		$stmt->bindValue ( 'teamSideId', $teamSideId, \PDO::PARAM_STR );
		$stmt->execute ();
	}

	private function calculateChampionTeamScore($gameId) {
		$conn = Application::instance ()->getConnection ();
		$scoreType = 'GAME';
		// Owned champions only SCORES_TEAMS_CHAMPS
		$stmt = $conn->prepare ( '
			INSERT INTO dfl_scores_teams_champs (`gameId`, `teamId`, `championId`, `championMultiplier`, `penalty`, `scoreValue`, `createdDate`) 
				SELECT 
					champscores.gameId, 
					teamchamps.teamId, 
					champs.championId, 
					champs.championMultiplier, 
					\'0\' AS `penalty`,
				
					ROUND(champscores.scoreValue + (champscores.scoreValue * (
					
							SELECT ((COUNT(*)-1)/(:maxPotentialChamps-1))*' . Config::$a ['fantasy'] ['team'] ['teammateBonusModifier'] . ' FROM dfl_team_champs AS `a`
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
				WHERE champscores.gameId = :gameId
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'maxPotentialChamps', Config::$a ['fantasy'] ['team'] ['maxPotentialChamps'], \PDO::PARAM_INT );
		$stmt->execute ();
		
		// Free champions only SCORES_TEAMS_CHAMPS
		$stmt = $conn->prepare ( '
			INSERT INTO dfl_scores_teams_champs (`gameId`, `teamId`, `championId`, `championMultiplier`, `penalty`, `scoreValue`, `createdDate`) 
				SELECT 
					champscores.gameId, 
					teamchamps.teamId, 
					champs.championId, 
					champs.championMultiplier, 
					:penalty AS `penalty`,
				
					ROUND((champscores.scoreValue*(1-:penalty)) + (champscores.scoreValue*(1-:penalty)) * (
					
							SELECT ((COUNT(*)-1)/(:maxPotentialChamps-1))*:teammateBonusModifier FROM dfl_team_champs AS `a`
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
				WHERE userchamps.championId IS NULL AND champscores.gameId = :gameId AND champs.championFree = 1
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'penalty', Config::$a ['fantasy'] ['team'] ['freeMultiplierPenalty'], \PDO::PARAM_STR );
		$stmt->bindValue ( 'maxPotentialChamps', Config::$a ['fantasy'] ['team'] ['maxPotentialChamps'], \PDO::PARAM_INT );
		$stmt->bindValue ( 'teammateBonusModifier', Config::$a ['fantasy'] ['team'] ['teammateBonusModifier'], \PDO::PARAM_STR );
		$stmt->execute ();
		
		// Total team scores SCORES_TEAMS
		$stmt = $conn->prepare ( '
			INSERT INTO dfl_scores_teams (`gameId`, `teamId`, `scoreValue`, `scoreType`, `createdDate`) 
			SELECT 
				teamchampscores.gameId AS `gameId`, 
				teamchampscores.teamId AS `teamId`, 
				SUM(teamchampscores.scoreValue) AS `scoreValue`, 
				:scoreType AS `scoreType`, 
				UTC_TIMESTAMP() AS `createdDate` 
				FROM dfl_scores_teams_champs AS `teamchampscores` 
			WHERE teamchampscores.gameId = :gameId 
			GROUP BY teamchampscores.teamId, teamchampscores.gameId	
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'scoreType', $scoreType, \PDO::PARAM_INT );
		$stmt->execute ();
	}

	/**
	 * This has the potential to become very slow, since it does a full re-calc for all teams
	 * Need to think of a way to reduce it
	 */
	public function calculateTeamScore($gameId = null) {
		$conn = Application::instance ()->getConnection ();
		if ($gameId == null) {
			$conn->executeQuery ( '
				UPDATE dfl_teams AS `teams`, ( 
					SELECT a.teamId, SUM(a.scoreValue) AS `total` 
					FROM dfl_scores_teams AS a
					GROUP BY a.teamId  
				) AS scoresteams 
				SET teams.scoreValue = scoresteams.total, teams.modifiedDate = UTC_TIMESTAMP()
				WHERE teams.teamId = scoresteams.teamId
			' );
		} else {
			// Quick dirty way when we have the game Id
			$stmt = $conn->prepare ( '
				UPDATE dfl_teams t 
					INNER JOIN (
					SELECT c.teamId, SUM(c.scoreValue) AS `total` 
					FROM dfl_scores_teams AS c
					WHERE c.gameId = :gameId
					GROUP BY c.teamId 
				) b ON b.teamId = t.teamId 
				SET t.scoreValue = t.scoreValue + b.total, t.modifiedDate = UTC_TIMESTAMP()
			' );
			$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
			$stmt->execute ();
		}
	}

	public function calculateTeamRanks() {
		$conn = Application::instance ()->getConnection ();
		$conn->executeQuery ( '
			INSERT IGNORE INTO dfl_team_ranks ( 
				SELECT 
					teams.teamId, 
					0 AS `rank` 
				FROM dfl_teams AS `teams`
			)
		' );
		$conn->executeQuery ( 'SET @rank=0' );
		$conn->executeQuery ( '
			UPDATE dfl_team_ranks AS `ranks`, ( 
				SELECT 
				@rank:=@rank+1 AS `teamRank`, 
				teams.teamId, 
				teams.scoreValue 
				FROM dfl_teams AS `teams` 
				ORDER BY teams.scoreValue DESC 
			) AS `teamranks` 
			SET ranks.teamRank = teamranks.teamRank 
			WHERE ranks.teamId = teamranks.teamId
		' );
	}

	public function resetGame($gameId) {
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_scores_champs', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_scores_teams', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_scores_teams_champs', array (
			'gameId' => $gameId 
		) );
		$conn->update ( 'dfl_games', array (
			'aggregated' => false 
		), array (
			'gameId' => $gameId 
		) );
	}

	public function removeGame($gameId) {
		$conn = Application::instance ()->getConnection ();
		$conn->delete ( 'dfl_games', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_games_champs', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_scores_champs', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_scores_teams', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_scores_teams_champs', array (
			'gameId' => $gameId 
		) );
		$conn->delete ( 'dfl_games_summoner_data', array (
			'gameId' => $gameId 
		) );
	}

	/**
	 * Update champion
	 * gamesWin & gamesLost & gamesPlayed
	 */
	public function updateChampionStats() {
		$conn = Application::instance ()->getConnection ();
		$conn->executeQuery ( '
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
		
		if (Config::$a ['fantasy'] ['updateChampMultiplier']) {
			$conn->executeQuery ( '
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
				SET 
				champs.championMultiplier = ROUND(1-champstats.playedRatio*champstats.WinRatio, 3)
			' );
		}
	}

}
