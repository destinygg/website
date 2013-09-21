<?php
namespace Destiny\LeagueofLegends\Utils;

use Destiny\Common\Config;

abstract class Lol {
	
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