<?php

namespace Destiny\Utils;

abstract class Http {
	const HEADER_ETAG = 'Etag';
	const HEADER_STATUS = 'Status';
	const HEADER_CONTENTLENGTH = 'Content-Length';
	const HEADER_CONTENTTYPE = 'Content-Type';
	const HEADER_LAST_MODIFIED = 'Last-Modified';
	const HEADER_CACHE_CONTROL = 'Cache-Control';
	const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
	const HEADER_LOCATION = 'Location';
	const HEADER_PRAGMA = 'Pragma';
	const HEADER_ACCEPT_RANGES = 'Accept-Ranges';
	const HEADER_CONNECTION = 'Connection';
	const STATUS_NOT_MODIFIED = 304;
	const STATUS_FORBIDDEN = 403;
	const STATUS_NOT_FOUND = 404;
	const STATUS_UNAUTHORIZED = 401;
	const STATUS_ERROR = 500;
	const STATUS_OK = 200;
	public static $HEADER_STATUSES = array (
			304 => 'Not Modified',
			500 => 'Error',
			404 => 'Not Found',
			401 => 'Unauthorized',
			200 => 'OK' 
	);

	public static function header($name, $value, $replace = true) {
		header ( $name . ': ' . $value, $replace );
	}

	public static function status($status) {
		header ( 'HTTP/1.1 ' . $status . ' ' . self::$HEADER_STATUSES [intval ( $status )] );
	}

	public static function auth($type, $realm) {
		header ( 'WWW-Authenticate: ' . $type . ' realm="' . $realm . '"' );
	}

	public static function sendFile($filename) {
		@ob_end_flush ();
		@flush ();
		readfile ( "$filename" );
		exit ();
	}

	public static function sendString($str) {
		@ob_end_flush ();
		@flush ();
		echo $str;
		exit ();
	}

	public static function sendNotModifiedResponse() {
		if (isset ( $_SERVER ['HTTP_IF_MODIFIED_SINCE'] ) && ! empty ( $_SERVER ['HTTP_IF_MODIFIED_SINCE'] )) {
			self::status ( self::STATUS_NOT_MODIFIED );
			self::header ( self::HEADER_CONNECTION, 'close' );
			@ob_end_flush ();
			@flush ();
			exit ();
		}
	}
	
	// Return FALSE if unmodified | TRUE if modified or we can't tell
	public static function checkIfModifiedSince($mtime, $send = false) {
		global $_SERVER;
		if (isset ( $_SERVER ['HTTP_IF_MODIFIED_SINCE'] ) && ! empty ( $_SERVER ['HTTP_IF_MODIFIED_SINCE'] )) {
			if ($_SERVER ['HTTP_IF_MODIFIED_SINCE'] == gmdate ( 'r', $mtime )) {
				if ($send) {
					self::header ( self::HEADER_LAST_MODIFIED, gmdate ( 'r', $mtime ) );
					self::sendNotModifiedResponse ();
				}
				return false;
			}
		}
		return true;
	}
	
	// Return FALSE if unmodified | TRUE if modified or we can't tell
	protected static function checkIfNoneMatch($etag, $send = false) {
		global $_SERVER;
		if (isset ( $_SERVER ['HTTP_IF_NONE_MATCH'] ) && ! empty ( $_SERVER ['HTTP_IF_NONE_MATCH'] )) {
			if ($_SERVER ['HTTP_IF_NONE_MATCH'] == $etag) {
				if ($send) {
					self::sendNotModifiedResponse ();
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * ### getBaseUrl function
	 * // utility function that returns base url for
	 *
	 * @return string
	 */
	public static function getBaseUrl() {
		$protocol = 'http';
		if ($_SERVER ['SERVER_PORT'] == 443 || (! empty ( $_SERVER ['HTTPS'] ) && strtolower ( $_SERVER ['HTTPS'] ) == 'on')) {
			$protocol .= 's';
			$protocol_port = $_SERVER ['SERVER_PORT'];
		} else {
			$protocol_port = 80;
		}
		$host = $_SERVER ['HTTP_HOST'];
		$port = $_SERVER ['SERVER_PORT'];
		$request = $_SERVER ['PHP_SELF'];
		return dirname ( $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request );
	}

}