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
	 * @param string $base
	 * @param string $filename
	 * @param string $version
	 */
	public static function load($filename, $version) {
		ob_start ();
		self::$a = require $filename;
		ob_end_clean ();
		if (! is_array ( self::$a )) {
			self::$a = array ();
		}
		// Add the version
		$version = parse_ini_file ( $version );
		self::$a ['version'] = $version ['version'];
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