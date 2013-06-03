<?php
namespace Destiny\Utils;

use Destiny\Utils\String\Params;
use Destiny\Utils\Options;


/**
 * Simple parameterized string utility
 */
class String {
	
	protected $value = '';
	protected $params = array ();

	public function __construct($value, array $args = null) {
		if (is_string ( $value )) {
			$this->value = $value;
			$this->params = $args;
		}
		Options::setOptions ( $this, $args );
	}

	public function __toString() {
		return Params::apply ( $this->value, $this->params );
	}

	public static function strictUTF8($string) {
		if (preg_match ( '%^(?:
			      [\x09\x0A\x0D\x20-\x7E]            # ASCII
			    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
			    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
			    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
			    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
			)*$%xs', $string )) {
			return $string;
		} else {
			return iconv ( 'CP1252', 'UTF-8', $string );
		}
	}

	public static function fileTail($file, $numLines = 100) {
		if(!is_file($file)){
			return '';
		}
		$fp = fopen ( $file, "r" );
		$chunk = 4096;
		$fs = sprintf ( "%u", filesize ( $file ) );
		$max = (intval ( $fs ) == PHP_INT_MAX) ? PHP_INT_MAX : filesize ( $file );
		$data = '';
		for($len = 0; $len < $max; $len += $chunk) {
			$seekSize = ($max - $len > $chunk) ? $chunk : $max - $len;
			
			fseek ( $fp, ($len + $seekSize) * - 1, SEEK_END );
			$data = fread ( $fp, $seekSize ) . $data;
			
			if (substr_count ( $data, "\n" ) >= $numLines + 1) {
				preg_match ( "!(.*?\n){" . ($numLines) . "}$!", $data, $match );
				fclose ( $fp );
				return $match [0];
			}
		}
		fclose ( $fp );
		return trim($data);
	}

}