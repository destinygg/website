<?php
namespace Destiny\Common;

abstract class Service {
	
	/**
	 * @var Service
	 */
	protected static $instance = null;

	/**
	 * @return $instance
	 */
	public static function instance() {
		if (static::$instance === null) {
			static::$instance = new static ();
		}
		return static::$instance;
	}

}