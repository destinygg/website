<?php

namespace Destiny;

abstract class Config {
	public static $a = null;

	public static function load($filename) {
		ob_start ();
		self::$a = require $filename;
		ob_end_clean ();
		self::setEnv ( self::$a ['env'] );
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