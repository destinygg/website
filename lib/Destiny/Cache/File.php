<?php
namespace Destiny\Cache;

use Destiny\Config;
use Destiny\Utils\Options;

/**
 * Simple abstraction of file caching logic
 */
class File {
	
	public $life = 0;
	public $filename = null;
	public $exists = null;
	public $lastModified = null;

	public function __construct($args) {
		Options::setOptions ( $this, $args );
		$this->exists = file_exists ( $this->filename );
		$this->lastModified = (($this->exists) ? filemtime ( $this->filename ) : 0);
	}

	public function exists() {
		return $this->exists;
	}

	public function cached() {
		return ($this->lastModified > 0 && (time () - $this->life) < $this->lastModified);
	}

	public function read() {
		return ($this->exists) ? file_get_contents ( $this->filename ) : '';
	}

	public function write($out) {
		$fp = fopen ( $this->filename . '.tmp', 'w' );
		fwrite ( $fp, $out );
		fclose ( $fp );
		$this->exists = rename ( $this->filename . '.tmp', $this->filename );
	}

	public function updateModifiedTime($time=null) {
		$this->lastModified = ($time == null) ? time () : $time;
		touch ( $this->filename, $this->lastModified );
		return $this->lastModified;
	}

	public function getLastModified(){
		return $this->lastModified;
	}

	public function clear() {
		if ($this->exists ()) {
			unlink ( $this->filename );
		}
	}

}
