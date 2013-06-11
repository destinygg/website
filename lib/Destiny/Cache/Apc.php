<?php

namespace Destiny\Cache;

use Destiny\Config;
use Destiny\Utils\Options;

/**
 * Simple abstraction of file caching logic
 */
class Apc {
	public $filename = '';
	public $life = 0;
	public $exists = false;
	public $lastModified = 0;

	public function __construct($args) {
		Options::setOptions ( $this, $args );
		// APC, we dont need the full path
		$this->filename = basename ( $args ['filename'] );
		$info = $this->getCacheInfo ();
		if (! empty ( $info )) {
			$this->lastModified = $info ['mtime'];
			$this->exists = ($info ['deletion_time'] > 0) ? false : true;
		}
	}

	public function read() {
		return apc_fetch ( $this->filename, $this->exists );
	}

	public function write($out) {
		$this->exists = true;
		return apc_store ( $this->filename, $out, Config::$a ['cache'] ['maxTTL'] );
	}

	public function exists() {
		return $this->exists;
	}

	public function cached() {
		return ($this->lastModified > 0 && (time () - $this->life) < $this->lastModified);
	}

	public function updateModifiedTime($time = null) {
		$this->write ( $this->read () );
		$this->lastModified = time ();
	}

	public function getLastModified() {
		return $this->lastModified;
	}

	public function clear() {
		apc_delete ( $this->filename );
	}

	private function getCacheInfo() {
		$nfo = apc_cache_info ( 'user' );
		if (! empty ( $nfo ['cache_list'] )) {
			foreach ( $nfo ['cache_list'] as $cache ) {
				if ($cache ['info'] == $this->filename) {
					return $cache;
				}
			}
		}
		return null;
	}

}