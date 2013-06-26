<?php

namespace Destiny\Utils;
/*
 * Abstract class to help set class properties using constructor arguments
 */
abstract class Options {

	public static function setOptions($object, array $options = null) {
		if (is_array ( $options )) {
			foreach ( $options as $key => $value ) {
				$method = 'set' . $key;
				if (method_exists ( $object, $method )) {
					$object->$method ( $value );
				} else if (property_exists ( $object, $key )) {
					$object->$key = $value;
				}
			}
		}
		return true;
	}

	public static function getOptions($object, array $options) {
		$r = array ();
		foreach ( $options as $name ) {
			$method = 'get' . $name;
			if (method_exists ( $object, 'get' . $name )) {
				$r [$name] = strval ( $object->$method () );
			}
		}
		return $r;
	}
	
}