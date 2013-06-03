<?php
namespace Destiny\Utils;

use Destiny\Config;

/**
 * This class is bad
 */
abstract class Cache {
	
	public static function clear($name){
		$cache = new Config::$a ['cache'] ['engine'] ( array ('filename' => $name) );
		$cache->clear ();
	}
	
}