<?php
namespace Destiny;

abstract class Config {
	
	/**
	 * The configuration array
	 *
	 * @var array
	 */
	public static $a = array ();

	/**
	 * Load the config stack
	 *
	 * @param array $array
	 */
	public static function load(array $config) {
		self::$a = $config;
		// Set environment vars
		if (isset ( self::$a ['env'] ) && ! empty ( self::$a ['env'] )) {
			foreach ( self::$a ['env'] as $i => $v ) {
				ini_set ( $i, $v );
			}
		}
	}

	/**
	 * Return the cdn domain
	 *
	 * @param string $protocol
	 * @return string
	 */
	public static function cdn($protocol = 'http://') {
		$domain = self::$a ['cdn'] ['domain'];
		return (! empty ( $domain )) ? $protocol . $domain : '';
	}

	/**
	 * Return the application version
	 *
	 * @return string
	 */
	public static function version() {
		return self::$a ['version'];
	}

}