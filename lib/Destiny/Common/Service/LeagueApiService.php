<?php
namespace Destiny\Common\Service;

use Destiny\Common\Service;
use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\CurlBrowser;
use Destiny\Common\Utils\String;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Exception;

class LeagueApiService extends Service {
	
	/**
	 * Singleton
	 *
	 * var LeagueApiService
	 */
	protected static $instance = null;

	/**
	 * Singleton
	 *
	 * @return LeagueApiService
	 */
	public static function instance() {
		return parent::instance ();
	}

	public function getStatus(array $options = array()) {
		return new CurlBrowser ( array_merge ( array (
			'url' => Config::$a ['lolapi'] ['url'],
			'contentType' => MimeType::JSON,
			'onfetch' => function ($json) {
				if (false == $json ['success'] && $json ['data'] != null) {
					throw new Exception ( 'LoL API down.' );
				}
				return $json ['data'];
			} 
		), $options ) );
	}

	public function getLeague(array $summoner) {
		$playerLeague = new CurlBrowser ( array (
			'url' => new String ( Config::$a ['lolapi'] ['url'] . '{summoner.region}/{summoner.name}/league?key={apikey}', array (
				'summoner.region' => $summoner ['region'],
				'summoner.name' => utf8_decode ( $summoner ['name'] ),
				'apikey' => Config::$a ['lolapi'] ['apikey'] 
			) ),
			'contentType' => MimeType::JSON,
			'params' => $summoner,
			'onfetch' => function ($league, $summoner) {
				if (false == $league ['success']) {
					throw new Exception ( 'LoL API down.' );
				}
				$league ['data'] ['rankInt'] = Lol::rankToInt ( $league ['data'] ['rank'] );
				return $league ['data'];
			} 
		) );
		return $playerLeague->getResponse ();
	}

	public function getRecentGames(array $summoner, $limit = 10, array $fileGamesId = null) {
		$games = new CurlBrowser ( array (
			'url' => new String ( Config::$a ['lolapi'] ['url'] . '{summoner.region}/{summoner.name}/games?key={apikey}&limit={limit}&gameIds={gameIds}', array (
				'summoner.region' => $summoner ['region'],
				'summoner.name' => utf8_decode ( $summoner ['name'] ),
				'apikey' => Config::$a ['lolapi'] ['apikey'],
				'limit' => $limit,
				'gameIds' => join ( ',', $fileGamesId ) 
			) ),
			'contentType' => MimeType::JSON 
		) );
		return $games->getResponse ();
	}

	public function getInGameProgress(array $summoner, $fileGameId = null) {
		$progress = new CurlBrowser ( array (
			'url' => new String ( Config::$a ['lolapi'] ['url'] . '{summoner.region}/{summoner.name}/ingame?key={apikey}&gameId={gameId}', array (
				'summoner.region' => $summoner ['region'],
				'summoner.name' => utf8_decode ( $summoner ['internalName'] ),
				'gameId' => $fileGameId,
				'apikey' => Config::$a ['lolapi'] ['apikey'] 
			) ),
			'contentType' => MimeType::JSON 
		) );
		return $progress->getResponse ();
	}

	public function getSummoner(array $summoner) {
		$playerSummoner = new CurlBrowser ( array (
			'url' => new String ( Config::$a ['lolapi'] ['url'] . '{summoner.region}/{summoner.name}?key={apikey}', array (
				'summoner.region' => $summoner ['region'],
				'summoner.name' => utf8_decode ( $summoner ['name'] ),
				'apikey' => Config::$a ['lolapi'] ['apikey'] 
			) ),
			'contentType' => MimeType::JSON,
			'onfetch' => function ($json) {
				if (false == $json ['success']) {
					throw new Exception ( 'LoL API down.' );
				}
				$json ['data'] ['revisionDate'] = Date::getDateTime ( floatval ( $json ['data'] ['revisionDate'] ) / 1000 )->format ( Date::FORMAT );
				return $json;
			} 
		) );
		$data = $playerSummoner->getResponse ();
		if ($data == null || $data ['success'] == false) {
			$data = $summoner;
		} else {
			$data = $data ['data'];
		}
		$data ['id'] = $summoner ['id'];
		$data ['region'] = Lol::getRegion ( $summoner ['region'] );
		return $data;
	}

	public function getSummoners(array $options = array()) {
		$lookupSummoners = Config::$a ['lol'] ['summoners'];
		$summoners = array ();
		foreach ( $lookupSummoners as $info ) {
			if (! $info ['public']) continue;
			$summoner = $this->getSummoner ( $info );
			$summoner ['region'] = Lol::getRegion ( $info ['region'] );
			$summoner ['league'] = ($info ['stats']) ? $this->getLeague ( $info ) : null;
			$summoners [] = $summoner;
		}
		usort ( $summoners, function ($a, $b) {
			if (! isset ( $a ['revisionDate'] )) {
				return true;
			}
			if (! isset ( $b ['revisionDate'] )) {
				return false;
			}
			return Date::getDateTime ( $a ['revisionDate'] ) < Date::getDateTime ( $b ['revisionDate'] );
		} );
		$response = $summoners;
		return $response;
	}

}