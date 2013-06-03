<?php
namespace Destiny\Service\Fantasy;

use Destiny\Service;
use Destiny\Application;
use Destiny\Config;
use Destiny\Utils\Date;
use Destiny\Utils\String;
use Destiny\Service\Fantasy\Db\Game;
use Destiny\Service\Fantasy\Db\Champion;
use Destiny\Service\Fantasy\Db\Leaderboard;
use Destiny\Service\Fantasy\Db\Challenge;

class Cache extends Service {
	
	/**
	 *
	 * @var Service
	 */
	protected static $instance = null;

	/**
	 *
	 * @return Destiny\Service\Fantasy\Cache
	 */
	public static function getInstance() {
		return parent::getInstance ();
	}

	/**
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function getRecentGames($options) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'recentgames' . $options ['limit'],
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			$response = $cache->read ();
		} else if (! $cache->cached ()) {
			$gameService = Game::getInstance ();
			$games = $gameService->getRecentGames ( $options ['limit'] );
			foreach ( $games as $i => $game ) {
				$games [$i] ['champions'] = $gameService->getGameChampions ( $game ['gameId'] );
				for($x = 0; $x < count ( $games [$i] ['champions'] ); $x ++) {
					$games [$i] ['champions'] [$x] ['summonerName'] = String::strictUTF8 ( $games [$i] ['champions'] [$x] ['summonerName'] );
				}
			}
			$response = json_encode ( $games );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response, TRUE );
	}

	public function getChampions(array $options = null) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'champions',
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			$response = $cache->read ();
		} else if (! $cache->cached ()) {
			$response = json_encode ( Champion::getInstance ()->getChampions () );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response );
	}
	
	public function getTeamLeaderboard(array $options = null, $limit = 1, $offset = 0) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'leadersboard'.$limit,
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read () );
		}
		if (! $cache->cached ()) {
			$champService = Champion::getInstance ();
			$teams = Leaderboard::getInstance ()->getTeamLeaderboard ( $limit, $offset );
			foreach ( $teams as $i => $team ) {
				$teams [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $team ['champions'] ) );
			}
			$response = json_encode ( $teams );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response );
	}
	
	public function getSubscriberTeamLeaderboard(array $options = null, $limit = 1, $offset = 0) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'leadersboardsubscribers'.$limit,
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read () );
		}
		if (! $cache->cached ()) {
			$champService = Champion::getInstance ();
			$teams = Leaderboard::getInstance ()->getSubscriberTeamLeaderboard ( $limit, $offset );
			foreach ( $teams as $i => $team ) {
				$teams [$i]->champions = $champService->getChampionsById ( explode ( ',', $team->champions ) );
			}
			$response = json_encode ( $teams );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response );
	}
	
	public function getRecentGameLeaderboard(array $options = null, $limit = 10, $offset = 0) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'recentgameleaderboard',
				'life' => 300 
		) );
		if (! $cache->cached ()) {
			$champService = Champion::getInstance ();
			$leaders = Leaderboard::getInstance ()->getRecentGameLeaderboard ( $limit, $offset );
			foreach ( $leaders as $i => $leader ) {
				$leaders [$i]->champions = $champService->getChampionsById ( explode ( ',', $leader->champions ) );
			}
			$response = json_encode ( $leaders );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response );
	}

	public function getCurrentWeekLeaderboard(array $options = null, $limit = 10) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'leaderboardbyweek',
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read (), TRUE );
		}
		if (! $cache->cached ()) {
			$champService = Champion::getInstance ();
			$range = Date::getWeekRange ( time () );
			$weekLeaders = Leaderboard::getInstance ()->getTeamRangeLeaderboard ( $range ['start'], $range ['end'], $limit );
			foreach ( $weekLeaders as $i => $weekTeam ) {
				$weekLeaders [$i] ['champions'] = $champService->getChampionsById ( explode ( ',', $weekTeam ['champions'] ) );
			}
			$response = json_encode ( $weekLeaders );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response, true );
	}

	public function getTopChampionScores(array $options = null, $limit = 10) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'topchampionscores',
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read (), TRUE );
		}
		if (! $cache->cached ()) {
			$topScorers = Leaderboard::getInstance ()->getTopChampionScores ( $limit );
			$response = json_encode ( $topScorers );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response, TRUE );
	}

	public function getTopTeamChampionScores(array $options = null, $limit = 10) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'topteamchampionscores',
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read (), TRUE );
		}
		if (! $cache->cached ()) {
			$topScorers = Leaderboard::getInstance ()->getTopTeamChampionScores ( $limit );
			$response = json_encode ( $topScorers );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response, TRUE );
	}

	public function getTopSummoners(array $options = null, $limit = 10) {
		$cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . 'topsummoners' . $limit,
				'life' => 300 
		) );
		if (isset ( $options ['cacheFirst'] ) && $options ['cacheFirst'] == true && $cache->exists ()) {
			return json_decode ( $cache->read (), TRUE );
		}
		if (! $cache->cached ()) {
			$champService = Champion::getInstance ();
			$summoners = Leaderboard::getInstance ()->getTopSummoners ( $limit );
			foreach ( $summoners as $i => $summoner ) {
				$summoners [$i]->summonerName = String::strictUTF8 ( $summoners [$i]->summonerName );
				$summoners [$i]->mostPlayedChampion = $champService->getChampionById ( $summoners [$i]->mostPlayedChampion );
			}
			$response = json_encode ( $summoners );
			$cache->write ( $response );
		} else {
			$response = $cache->read ();
		}
		return json_decode ( $response );
	}
	
	private $invites = array();
	
	/**
	 * @param int $teamId
	 * @param int $limit
	 * @return array
	 */
	public function getInvites($teamId, $limit) {
		if (! isset ( $this->invites [$teamId] )) {
			$this->invites [$teamId] = array ();
			$this->invites [$teamId] = Challenge::getInstance ()->getInvites ( $teamId, $limit );
		}
		return $this->invites [$teamId];
	}
	
}