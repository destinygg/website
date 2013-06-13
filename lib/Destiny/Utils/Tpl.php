<?php

namespace Destiny\Utils;

use Destiny\Utils\Country;
use Destiny\Config;

class Tpl {

	public static function out($var, $default = null) {
		return htmlentities ( ((empty ( $var )) ? $default : $var), ENT_QUOTES, 'UTF-8' );
	}

	public static function flag($code) {
		$country = Country::getCountryByCode ( $code );
		return (! empty ( $country )) ? '<i title="' . self::out ( $country ['name'] ) . '" class="flag flag-' . self::out ( strtolower ( $code ) ) . '"></i>' : '';
	}

	public static function n($number) {
		return number_format ( $number );
	}

	public static function subIcon($output) {
		return ($output) ? '<i class="icon-bobross" title="Subscriber"></i>' : '';
	}

	public static function title($title) {
		$str = Config::$a ['meta'] ['title'];
		if (! empty ( $title )) {
			$str = sprintf ( '%s : %s', Config::$a ['meta'] ['shortName'], $title );
		}
		return $str;
	}

	public static function currency($currencyCode, $amount) {
		$amount = ($amount == null || ! is_numeric ( $amount )) ? '0.00' : number_format ( $amount, 2 );
		if (isset ( Config::$a ['commerce'] ['currencies'] [$currencyCode] )) {
			$symbol = Config::$a ['commerce'] ['currencies'] [$currencyCode] ['symbol'];
			return $symbol . $amount . ' ' . $currencyCode;
		}
		return $currencyCode . ' ' . $amount;
	}

}