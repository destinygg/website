<?php

namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Utils\Date;
use Destiny\AppException;

class GameTrackingService extends Service {
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return GameTrackingService
	 */
	public static function instance() {
		return parent::instance ();
	}

	/**
	 * This method is called multiple times for a single game Because the api method generates the stats in the context of the requested summoner
	 *
	 * @param int $game
	 * @param array $summoner
	 * @return boolean
	 */
	public function persistGame($game, array $summoner) {
		if (! isset ( $game ['statistics'] )) {
			return false;
		}
		
		// Setting generic win / loss / teams
		$game ['gameWin'] = 0;
		foreach ( $game ['statistics'] as $statType => $statValue ) {
			if ($statType == 'WIN' || $statType == 'LOSE') {
				$game ['gameWin'] = ($statType == 'WIN') ? 1 : 0;
				break;
			}
		}
		$homeTeamId = $game ['playerTeamId'];
		$awayTeamId = $game ['enemyTeamId'];
		$game ['gameWinSideId'] = ($game ['gameWin']) ? $homeTeamId : $awayTeamId;
		$game ['gameLoseSideId'] = ($game ['gameWin']) ? $awayTeamId : $homeTeamId;
		//
		
		if (! $this->isGameRecorded ( $game ['gameId'] )) {
			$this->insertGame ( $game, $summoner );
			$this->insertGameChampsData ( $game );
		}
		// This is summoner specific and must be kept separate from the game record
		if (! $this->isSummonerGameDataRecorded ( $game ['gameId'], $summoner ['id'] )) {
			$this->insertSummonerGameData ( $game, $summoner );
		}
	}

	public function insertGame($game, $summoner) {
		$conn = Application::instance ()->getConnection ();
		// If the start time wasnt recorded with the inGame service, fall back to the weird record create date LOL servers sends us.
		$stmt = $conn->prepare ( 'SELECT gameStartTime FROM dfl_ingame_progress WHERE gameId = :gameId LIMIT 0,1' );
		$stmt->bindValue ( 'gameId', $game ['gameId'], \PDO::PARAM_INT );
		$stmt->execute ();
		$gameStartTime = $stmt->fetchColumn ();
		$gameEndTime = null;
		if (empty ( $gameStartTime )) {
			$gameEndTime = Date::getDateTime ( ($game ['createDate'] / 1000) )->format ( 'Y-m-d H:i:s' );
			$gameStartTime = $gameEndTime;
		} else {
			$gameEndTime = Date::getDateTime ( ($game ['createDate'] / 1000) + intval ( $game ['timeInQueue'] ) )->format ( 'Y-m-d H:i:s' );
		}
		$conn->insert ( 'dfl_games', array (
				'gameId' => $game ['gameId'],
				'gameCreatedDate' => $gameStartTime,
				'gameEndDate' => $gameEndTime,
				'gameType' => $game ['queue'],
				'gameRanked' => 1,
				'gameLoseSideId' => $game ['gameLoseSideId'],
				'gameWinSideId' => $game ['gameWinSideId'],
				'gameSeason' => Config::$a ['fantasy'] ['season'],
				'gameRegion' => $summoner ['region'],
				'aggregated' => 0,
				'aggregatedDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ),
				'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
		) );
	}

	public function getTrackedGames($limit = 1) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT ingame.* 
			FROM dfl_ingame_progress AS `ingame`
			ORDER BY ingame.gameStartTime DESC
			LIMIT 0,:limit
		' );
		$stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
		$stmt->execute ();
		$games = $stmt->fetchAll ();
		for($i = 0; $i < count ( $games ); $i ++) {
			if (! empty ( $games [$i] ['gameData'] )) {
				$games [$i] ['gameData'] = json_decode ( $games [$i] ['gameData'], true );
			}
		}
		return $games;
	}

	public function getTrackedProgressById($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT ingame.* 
			FROM dfl_ingame_progress AS `ingame`
			WHERE gameId = :gameId 
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
		$game = $stmt->fetch ();
		if (! empty ( $game ['gameData'] )) {
			$game ['gameData'] = json_decode ( $game ['gameData'], true );
		}
		return $game;
	}

	/**
	 * Use this to record the correct game start time
	 * to set the correct start times.
	 *
	 * @param stdObj $game
	 */
	public function trackIngameProgress($summoner, $game) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT COUNT(*) FROM dfl_ingame_progress WHERE gameId = :gameId LIMIT 0,1' );
		$stmt->bindValue ( 'gameId', $game ['gameId'], \PDO::PARAM_INT );
		$stmt->execute ();
		if (intval ( $stmt->fetchColumn () ) == 0) {
			$game ['gameStartTime'] = null;
			// Weird way of getting the time the summoner started the que
			// because the LOL servers refuse to send the start time of the actual game in any feed
			for($i = 0; $i < count ( $game ['gameSummonerSelections'] ); ++ $i) {
				if ($game ['gameSummonerSelections'] [$i] ['summonerId'] == $summoner ['id']) {
					// Records the start time as the moment the game was found
					$startTime = Date::getDateTime ();
					if ($game ['gameSummonerSelections'] [$i] ['timeAddedToQueue'] != null) {
						$startTime->setTimestamp ( $game ['gameSummonerSelections'] [$i] ['timeAddedToQueue'] / 1000 );
					}
					$game ['gameStartTime'] = $startTime->format ( 'Y-m-d H:i:s' );
				}
			}
			if ($game ['gameStartTime'] == null) {
				throw new AppException ( 'GameStartTime could not be retrieved' );
			}
			$conn->insert ( 'dfl_ingame_progress', array (
					'gameId' => $game ['gameId'],
					'gameStartTime' => $game ['gameStartTime'],
					'gameData' => json_encode ( $game ) 
			) );
			return $conn->lastInsertId ();
		}
		return false;
	}

	public function insertSummonerGameData($game, $summoner) {
		$conn = Application::instance ()->getConnection ();
		$conn->insert ( 'dfl_games_summoner_data', array (
				'gameId' => $game ['gameId'],
				'gameData' => json_encode ( $game ),
				'summonerId' => $summoner ['id'],
				'gameWin' => $game ['gameWin'] 
		) );
	}

	public function isSummonerGameDataRecorded($gameId, $summonerId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( '
			SELECT COUNT(*) FROM dfl_games_summoner_data 
			WHERE gameId = :gameId AND  summonerId = :summonerId 
			LIMIT 0,1
		' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->bindValue ( 'summonerId', $summonerId, \PDO::PARAM_INT );
		$stmt->execute ();
		return (intval ( $stmt->fetchColumn () ) == 1);
	}

	public function insertGameChampsData($game) {
		$conn = Application::instance ()->getConnection ();
		foreach ( $game ['gameTeams'] as $teamSideId => $team ) {
			foreach ( $team as $summoner ) {
				$stmt = $conn->prepare ( '
					INSERT INTO dfl_games_champs SET
						gameId = :gameId,
						championId = :championId,
						teamSideId = :teamSideId,
						summonerId = :summonerId,
						summonerName = :summonerName
					ON DUPLICATE KEY UPDATE gameId=gameId
				' );
				$stmt->bindValue ( 'gameId', $game ['gameId'], \PDO::PARAM_INT );
				$stmt->bindValue ( 'championId', $game ['gameSummonerSelections'] [$summoner ['summonerId']] ['id'], \PDO::PARAM_INT );
				$stmt->bindValue ( 'teamSideId', $teamSideId, \PDO::PARAM_STR );
				$stmt->bindValue ( 'summonerId', $summoner ['summonerId'], \PDO::PARAM_INT );
				$stmt->bindValue ( 'summonerName', $summoner ['name'], \PDO::PARAM_STR );
				$stmt->execute ();
			}
		}
	}

	public function isGameRecorded($gameId) {
		$conn = Application::instance ()->getConnection ();
		$stmt = $conn->prepare ( 'SELECT COUNT(*) FROM dfl_games WHERE gameId = :gameId LIMIT 0,1' );
		$stmt->bindValue ( 'gameId', $gameId, \PDO::PARAM_INT );
		$stmt->execute ();
		return (intval ( $stmt->fetchColumn () ) == 1);
	}

}
