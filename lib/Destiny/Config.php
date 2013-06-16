<?php

namespace Destiny;

abstract class Config {
	public static $a = array ();

	public static function load($filename, $version) {
		ob_start ();
		self::$a = require $filename;
		ob_end_clean ();
		if (! is_array ( self::$a )) {
			self::$a = array ();
		}
		self::$a = array_merge_recursive ( self::$a, $version );
		if (isset ( self::$a ['env'] ) && ! empty ( self::$a ['env'] )) {
			self::setEnv ( self::$a ['env'] );
		}
	}

	protected static function setEnv(array $args) {
		foreach ( $args as $i => $v ) {
			ini_set ( $i, $v );
		}
	}

	public static function cdn($protocol = 'http://') {
		$domain = self::$a ['cdn'] ['domain'];
		return (! empty ( $domain )) ? $protocol . $domain : '';
	}

	public static function version() {
		return self::$a ['version'];
	}

}