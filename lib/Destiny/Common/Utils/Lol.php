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

}