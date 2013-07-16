<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Config;

abstract class Lol {
	
	/**
	 * The various game types
	 *
	 * @var array
	 */
	public static $gameTypes = array (
		'NONE' => 'None',
		'BOT' => 'Bot',
		'BOT_3x3' => 'Bot 3v3',
		'NORMAL' => 'Normal',
		'NORMAL_3x3' => 'Normal 3v3',
		'ODIN_UNRANKED' => 'Odin Unranked',
		'RANKED_SOLO_5x5' => 'Ranked Solo 5v5',
		'RANKED_TEAM_3x3' => 'Ranked Team 3v3',
		'RANKED_TEAM_5x5' => 'Ranked Team 5v5',
		'ARAM_UNRANKED_5x5' => 'ARAM Unranked 5v5' 
	);
	
	/**
	 * The blue team side
	 *
	 * @var int
	 */
	const TEAMSIDE_BLUE = 100;
	
	/**
	 * Purple side id
	 *
	 * @var int
	 */
	const TEAMSIDE_PURPLE = 200;

	/**
	 * Team side name
	 *
	 * @param int $teamSideId
	 * @return string $teamSideId
	 */
	public static function getTeamSideName($teamSideId) {
		if ($teamSideId == self::TEAMSIDE_BLUE) {
			return 'BLUE';
		}
		if ($teamSideId == self::TEAMSIDE_PURPLE) {
			return 'PURPLE';
		}
		return $teamSideId;
	}

	/**
	 * Rank to int
	 *
	 * @param string $rank
	 * @return number
	 */
	public static function rankToInt($rank) {
		switch ($rank) {
			case 'I' :
				return 1;
			case 'II' :
				return 2;
			case 'III' :
				return 3;
			case 'IV' :
				return 4;
			case 'V' :
				return 5;
			case 'VI' :
				return 6;
			case 'VII' :
				return 7;
		}
	}

	/**
	 *
	 * @param string $id
	 * @return array
	 */
	public static function getRegion($id) {
		$id = strtolower ( $id );
		return array (
			'id' => $id,
			'label' => Config::$a ['lol'] ['regions'] [$id] 
		);
	}

	/**
	 *
	 * @param array $game
	 * @param array $champ
	 * @param array $scores
	 * @return array
	 */
	public static function getGameChampionPoints(array $game, array $champ, array $scores) {
		$points = 0;
		foreach ( $scores as $score ) {
			if (( int ) $game ['gameId'] == ( int ) $score ['gameId'] && ( int ) $score ['championId'] == ( int ) $champ ['championId']) {
				$score = intval ( $score ['scoreValue'] );
				$points = ($score > 0) ? '<span style="color: #1a6f00;">+' . $score . '</span>' : (($score < 0) ? '<span style="color: #8a1919;">' . $score . '</span>' : $score);
			}
		}
		return $points;
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public static function getIcon($name) {
		return Config::cdn () . '/web/img/lol/champions/' . strtolower ( preg_replace ( '/([^\d\w\-]+)/i', '', str_replace ( ' ', '-', $name ) ) ) . '.png';
	}

	/**
	 * Find and return the champion pick
	 * Because of the strange way lol servers give back the data
	 *
	 * @param array $game
	 * @param array $summoner
	 * @return array || NULL
	 */
	public static function getChampPick(array $game, array $summoner) {
		foreach ( $game ['gameSummonerSelections'] as $selection ) {
			if ($selection ['summonerId'] == $summoner ['summonerId']) {
				return $selection;
			}
		}
		return null;
	}

}