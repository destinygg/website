<?php

namespace Destiny\Utils;

use Destiny\Application;
use Destiny\Config;

/**
 * This class is weird
 */
abstract class Country {
	
	/**
	 * List of countries e.g.
	 * [{"name":"Afghanistan","alpha-2":"AF","country-code":"004"},...] countries
	 *
	 * @var array
	 */
	public static $countries = array ();
	
	/**
	 * List of countries by code e.g.
	 * {"AF":"Afghanistan"} countries
	 *
	 * @var array
	 */
	public static $codeIndex = null;

	/**
	 * Return a cached list of countries
	 *
	 * @return array
	 */
	public static function getCountries() {
		if (self::$countries == null) {
			$cache = Application::instance ()->getMemoryCache ( array (
					'filename' => Config::$a ['cache'] ['path'] . 'geodata',
					'life' => 1 * 30 * 24 * 60 * 60 
			) );
			if (! $cache->cached ()) {
				$countries = json_decode ( file_get_contents ( Config::$a ['geodata'] ), true );
				$cache->write ( $countries );
			} else {
				$countries = $cache->read ();
			}
			if (is_array ( $countries )) {
				self::$countries = $countries;
			}
		}
		if (empty ( self::$codeIndex )) {
			self::buildIndex ();
		}
		return self::$countries;
	}

	private static function buildIndex() {
		foreach ( self::$countries as $i => $country ) {
			self::$codeIndex [strtolower ( $country ['alpha-2'] )] = $i;
		}
	}

	public static function getCountryByCode($code) {
		$code = strtolower ( $code );
		$countries = self::getCountries ();
		return (isset ( self::$codeIndex [$code] )) ? $countries [self::$codeIndex [$code]] : null;
	}

}