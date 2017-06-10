<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Config;

class Tpl {

    public static function jsout($var) {
        return json_encode ( $var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    }

    public static function out($var, $default = "") {
        return htmlentities ( ((empty ( $var )) ? $default : $var), ENT_QUOTES, 'UTF-8' );
    }

    public static function flag($code) {
        $country = Country::getCountryByCode ( $code );
        return (! empty ( $country )) ? '<i title="' . self::out ( $country ['name'] ) . '" class="flag flag-' . self::out ( strtolower ( $code ) ) . '"></i>' : '';
    }

    public static function n($number) {
        return number_format ( $number );
    }

    public static function title($title) {
        $str = Config::$a ['meta'] ['title'];
        if (! empty ( $title )) {
            $str = sprintf ( '%s - %s', $title, Config::$a ['meta'] ['shortName'] );
        }
        return $str;
    }

    public static function mask($str, $padStr = '*', $show = 6, $pad = 10) {
        if (strlen ( $str ) >= $show) {
            $str = substr ( $str, 0, $show );
        }
        return self::out ( str_pad ( $str, $pad, $padStr ) );
    }

    public static function moment(\DateTime $date, $format, $momentFormat = 'MMMM Do, h:mm:ss a, YYYY') {
        return sprintf ( '<time title="%s" data-moment="true" datetime="%s" data-format="%s">%s</time>', $date->format ( Date::STRING_FORMAT ), $date->format ( Date::FORMAT ), $momentFormat, $date->format ( $format ) );
    }

    public static function fromNow(\DateTime $date, $momentFormat = 'MMMM Do, h:mm:ss a, YYYY') {
        return sprintf ( '<time title="%s" data-moment="true" data-moment-fromnow="true" datetime="%s" data-format="%s">%s</time>', $date->format ( Date::STRING_FORMAT ), $date->format ( Date::FORMAT ), $momentFormat, Date::getElapsedTime ( $date ) );
    }

    public static function calendar(\DateTime $date, $momentFormat = 'MMMM Do, h:mm:ss a, YYYY') {
        return sprintf ( '<time title="%s" data-moment="true" data-moment-calendar="true" datetime="%s" data-format="%s">%s</time>', $date->format ( Date::STRING_FORMAT ), $date->format ( Date::FORMAT ), $momentFormat, Date::getElapsedTime ( $date ) );
    }

}