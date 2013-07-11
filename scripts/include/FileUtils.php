<?php
use Destiny\AppException;

use Monolog\Logger;
class FileUtils {
	
	/**
	 * The base folder
	 * @var string
	 */
	public static $b = '';
	
	/**
	 * Log
	 * @var Logger
	 */
	public static $log = null;
	
	/**
	 * The url for the compression service
	 * @var string
	 */
	public static $compressionUrl = 'http://shrinkassets.elasticbeanstalk.com';

	/**
	 * Join files together and save to $dest path
	 *
	 * @param string $dest
	 * @param array $files
	 */
	public static function concat($dest, array $files) {
		self::$log->info ( sprintf ( 'Concat [%s]', $dest ), $files );
		foreach ( $files as $file ) {
			file_put_contents ( self::$b . $dest, file_get_contents ( self::$b . $file ) . PHP_EOL . PHP_EOL, FILE_APPEND );
		}
	}

	/**
	 * Compress a file
	 * @param string $file
	 */
	public static function compress($file) {
		self::$log->info ( sprintf ( 'Compress [%s]', $file ) );
		$ext = strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) );
		$url = self::$compressionUrl . "/" . $ext . "/file";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt ( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt ( $ch, CURLOPT_POST, TRUE );
		if ($ext == 'js') {
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
				"Content-Type: text/javascript; charset=UTF-8" 
			) );
		}
		if ($ext == 'css') {
			curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
				"Content-Type: text/css; charset=UTF-8" 
			) );
		}
		curl_setopt ( $ch, CURLOPT_ENCODING, 'UTF-8' );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, file_get_contents ( self::$b . $file ) );
		$response = curl_exec ( $ch );
		$info = curl_getinfo ( $ch );
		self::$log->info ( sprintf ( 'Curl [%s] %s', $info ["http_code"], $url ) );
		curl_close ( $ch );
		if ($info ["http_code"] != 200) {
			self::delete ( $file );
			file_put_contents ( self::$b . $file, $response );
			self::$log->info ( sprintf ( 'Put file [%s]', $file ) );
			return true;
		}
		return false;
	}

	/**
	 * Delete a file
	 * @param unknown_type $file
	 */
	public static function delete($file) {
		self::$log->info ( sprintf ( 'Delete [%s]', $file ) );
		@unlink ( self::$b . $file );
	}

	/**
	 * Copy a file
	 * @param string $source
	 * @param string $dest
	 * @return boolean
	 */
	public static function copy($source, $dest) {
		self::$log->info ( sprintf ( 'Copy [%s] to [%s]', $source, $dest ) );
		return copy ( self::$b . $source, self::$b . $dest );
	}

}