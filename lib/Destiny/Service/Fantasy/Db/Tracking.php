<?php

namespace Destiny\Service\Fantasy\Db;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Cache;
use Destiny\Utils\Date;

class Tracking extends Service {
	protected static $instance = null;

	/**
	 *
	 * @return Tracking
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}
	
	/*
	 * This method may be called multiple times for a single game Because the api method generates the stats in the context of the requested summoner
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
		
		if (false == $this->isGameRecorded ( $game ['gameId'] )) {
			$this->insertGame ( $game, $summoner );
			$this->insertGameChampsData ( $game, $summoner );
		}
		// This is summoner specific
		if (false == $this->isGameDataRecorded ( $game ['gameId'], $summoner ['acctId'], $summoner ['id'] )) {
			$this->insertSummonerGameData ( $game, $summoner );
		}
	}

	public function getTrackedGames($limit = 1) {
		$db = Application::getInstance ()->getDb ();
		$games = $db->select ( '
				SELECT 
					ingame.* 
				FROM dfl_ingame_progress AS `ingame`
				ORDER BY ingame.gameStartTime DESC
				LIMIT 0,{limit}', array (
				'limit' => $limit 
		) )->fetchRows ();
		for($i = 0; $i < count ( $games ); $i ++) {
			if (! empty ( $games [$i] ['gameData'] )) {
				$games [$i] ['gameData'] = json_decode ( $games [$i] ['gameData'], true );
			}
		}
		return $games;
	}

	public function insertGame($game, $summoner) {
		$db = Application::getInstance ()->getDb ();
		// If the start time wasnt recorded with the inGame service, fall back to the weird record create date LOL servers sends us.
		$gameStartTime = $db->select ( 'SELECT gameStartTime FROM dfl_ingame_progress WHERE gameId = \'{gameId}\' LIMIT 0,1', array (
				'gameId' => $game ['gameId'] 
		) )->fetchValue ();
		$gameEndTime = null;
		if (empty ( $gameStartTime )) {
			$gameEndTime = Date::getDateTime ( ($game ['createDate'] / 1000), 'Y-m-d H:i:s' );
			$gameStartTime = $gameEndTime;
		} else {
			$gameEndTime = Date::getDateTime ( ($game ['createDate'] / 1000) + intval ( $game ['timeInQueue'] ), 'Y-m-d H:i:s' );
		}
		$db->insert ( '
			INSERT INTO dfl_games 
			(`gameId`,`gameCreatedDate`,`gameEndDate`,`gameType`,`gameRanked`,`gameLoseSideId`,`gameWinSideId`,`gameSeason`,`gameRegion`,`aggregated`,`aggregatedDate`,`createdDate`) VALUES 
			(\'{gameId}\',\'{gameCreatedDate}\',\'{gameEndDate}\',\'{gameType}\',\'{gameRanked}\',\'{gameLoseSideId}\',\'{gameWinSideId}\',\'{gameSeason}\',\'{gameRegion}\',\'{aggregated}\',UTC_TIMESTAMP(),UTC_TIMESTAMP())', array (
				'gameId' => $game ['gameId'],
				'gameCreatedDate' => $gameStartTime,
				'gameEndDate' => $gameEndTime,
				'gameType' => $game ['queue'],
				'gameRanked' => '1',
				'gameLoseSideId' => $game ['gameLoseSideId'],
				'gameWinSideId' => $game ['gameWinSideId'],
				'gameSeason' => Config::$a ['fantasy'] ['season'],
				'gameRegion' => $summoner ['region'],
				'aggregated' => '0' 
		) );
	}

	/**
	 * Use this to record the correct game start time
	 * to set the correct start times.
	 *
	 * @param stdObj $game
	 */
	public function trackIngameProgress($summoner, $game) {
		$db = Application::getInstance ()->getDb ();
		$gameRecorded = (( int ) $db->select ( 'SELECT COUNT(*) FROM dfl_ingame_progress WHERE gameId = \'{gameId}\' LIMIT 0,1', array (
				'gameId' => ( int ) $game ['gameId'] 
		) )->fetchValue () == 1) ? true : false;
		if ($gameRecorded == false) {
			$game ['gameStartTime'] = null;
			// Weird way of getting the time the summoner started the que
			// because there LOL servers refuse to send the start time of the actual game
			for($i = 0; $i < count ( $game ['gameSummonerSelections'] ); ++ $i) {
				if ($game ['gameSummonerSelections'] [$i] ['summonerId'] == $summoner ['id']) {
					if ($game ['gameSummonerSelections'] [$i] ['timeAddedToQueue'] != null) {
						$time = $game ['gameSummonerSelections'] [$i] ['timeAddedToQueue'] / 1000;
					} else {
						// Records the start time as the moment the game was found
						$time = time ();
					}
					$game ['gameStartTime'] = Date::getDateTime ( $time )->format ( 'Y-m-d H:i:s' );
				}
			}
			if ($game ['gameStartTime'] == null) {
				throw new \Exception ( 'GameStartTime could not be retrieved' );
			}
			//
			$db->insert ( '
				INSERT INTO dfl_ingame_progress 
				(`gameId`,`gameStartTime`,`gameData`)
				VALUES 
				(\'{gameId}\',\'{gameStartTime}\',\'{gameData}\')
				', array (
					'gameId' => $game ['gameId'],
					'gameStartTime' => $game ['gameStartTime'],
					'gameData' => json_encode ( $game ) 
			) );
		}
	}

	public function insertSummonerGameData($game, $summoner) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
			INSERT INTO dfl_games_summoner_data 
				(`gameId`,`gameData`,`acctId`,`summonerId`,`gameWin`) VALUES 
				( \'{gameId}\',\'{gameData}\',\'{acctId}\',\'{summonerId}\',\'{gameWin}\')', array (
				'gameId' => $game ['gameId'],
				'gameData' => json_encode ( $game ),
				'acctId' => $summoner ['acctId'],
				'summonerId' => $summoner ['id'],
				'gameWin' => ($game ['gameWin'] == true) ? '1' : '0' 
		) );
	}

	public function insertGameChampsData($gameData, array $summoner) {
		$db = Application::getInstance ()->getDb ();
		foreach ( $gameData ['gameTeams'] as $teamId => $team ) {
			foreach ( $team as $teamSummoner ) {
				$this->insertGameChamp ( array (
						'gameId' => $gameData ['gameId'],
						'championId' => $gameData ['gameSummonerSelections'] [$teamSummoner ['summonerId']] ['id'],
						'teamSideId' => $teamId,
						'summonerId' => $teamSummoner ['summonerId'],
						'summonerName' => $teamSummoner ['name'] 
				) );
			}
		}
	}

	public function insertGameChamp(array $data) {
		$db = Application::getInstance ()->getDb ();
		$db->insert ( '
			INSERT INTO dfl_games_champs SET 
				teamSideId = \'{teamSideId}\', 
				gameId = \'{gameId}\', 
				championId = \'{championId}\', 
				summonerId = \'{summonerId}\', 
				summonerName = \'{summonerName}\'
			ON DUPLICATE KEY UPDATE gameId=gameId
			', $data );
	}

	public function isGameDataRecorded($gameId, $acctId, $summonerId) {
		$db = Application::getInstance ()->getDb ();
		return (( int ) $db->select ( '
				SELECT COUNT(*) FROM dfl_games_summoner_data WHERE 
					gameId = \'{gameId}\' AND 
					acctId = \'{acctId}\' AND 
					summonerId = \'{summonerId}\' 
				LIMIT 0,1', array (
				'gameId' => ( int ) $gameId,
				'acctId' => ( int ) $acctId,
				'summonerId' => ( int ) $summonerId 
		) )->fetchValue () == 1) ? true : false;
	}

	public function isGameRecorded($gameId) {
		$db = Application::getInstance ()->getDb ();
		return (( int ) $db->select ( 'SELECT COUNT(*) FROM dfl_games WHERE gameId = \'{gameId}\' LIMIT 0,1', array (
				'gameId' => ( int ) $gameId 
		) )->fetchValue () == 1) ? true : false;
	}

}
