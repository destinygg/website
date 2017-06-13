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

    public static function number($number) {
        return number_format ( $number );
    }

    public static function title($title) {
        $str = Config::$a ['meta'] ['title'];
        if (! empty ( $title )) {
            $str = sprintf ( '%s - %s', $title, Config::$a ['meta'] ['shortName'] );
        }
        return $str;
    }

    public static function moment(\DateTime $date, $format, $momentFormat = 'MMMM Do, h:mm:ss a, YYYY') {
        return sprintf ( '<time title="%s" data-moment="true" datetime="%s" data-format="%s">%s</time>', $date->format ( Date::STRING_FORMAT ), $date->format ( Date::FORMAT ), $momentFormat, $date->format ( $format ) );
    }

    public static function fromNow(\DateTime $date, $momentFormat = 'MMMM Do, h:mm:ss a, YYYY') {
        return sprintf ( '<time title="%s" data-moment="true" data-moment-fromnow="true" datetime="%s" data-format="%s">%s</time>', $date->format ( Date::STRING_FORMAT ), $date->format ( Date::FORMAT ), $momentFormat, Date::getElapsedTime ( $date ) );
    }

}