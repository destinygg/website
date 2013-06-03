<?php
namespace Destiny;

class Logger {
	
	public $filename = '';
	public $lastModified = 0;

	function __construct($filename) {
		$this->filename = $filename;
		$this->lastModified = (is_file ( $filename )) ? filemtime ( $this->filename ) : 0;
		if (is_file ( $this->filename ) == false) {
			error_log ( "\n", 3, $this->filename );
		}
	}

	public function log($msg) {
		error_log ( $this->getTime () . '[' . $this->getUserIp () . ']: ' . $msg . "\n", 3, $this->filename );
	}

	public function touch() {
		touch ( $this->filename );
	}

	public function getLastModified() {
		return $this->lastModified;
	}

	private function getTime() {
		return gmdate ( 'Y-m-d H:i:s', time () );
	}

	/**
	 * This is awkard, and doesnt belong here, I do this so that I can
	 * I dont get a huge log file
	 */
	public function clearLog($msg = '') {
		$fp = fopen ( $this->filename, 'w+' );
		fwrite ( $fp, $this->getTime () . ': ' . $msg . "\n" );
		fclose ( $fp );
	}

	private function getUserIp() {
		if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) {
			$ip = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}else{
			$ip = 'LOCAL';
		}
		return $ip;
	}

}