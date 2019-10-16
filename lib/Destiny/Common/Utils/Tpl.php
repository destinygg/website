<?php
namespace Destiny\Common\Utils;

use DateTime;
use Destiny\Common\Config;
use Exception;

class Tpl {

    public static function jsout($var) {
        return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    public static function out($var, string $default = null): string {
        if ($var instanceof Exception) {
            $var = $var->getMessage();
        }
        return htmlentities(((empty ($var)) ? $default : $var), ENT_QUOTES, 'UTF-8');
    }

    public static function number($number) {
        return number_format($number);
    }

    public static function title($title): string {
        $title = trim("$title");
        if (!empty($title)) {
            $str = sprintf('%s - %s', $title, Config::$a['meta']['shortName']);
        } else {
            $str = Config::$a['meta']['title'];
        }
        return "<title>$str</title>\r\n";
    }

    public static function moment(DateTime $date, string $format = null, string $momentFormat = 'MMMM Do, h:mm:ss a, YYYY'): string {
        return sprintf('<time title="%s" data-moment="true" datetime="%s" data-format="%s">%s</time>', $date->format(Date::STRING_FORMAT), $date->format(Date::FORMAT), $momentFormat, $date->format($format));
    }

    public static function fromNow(DateTime $date, string $momentFormat = 'MMMM Do, h:mm:ss a, YYYY'): string {
        return sprintf('<time title="%s" data-moment="true" data-moment-fromnow="true" datetime="%s" data-format="%s">%s</time>', $date->format(Date::STRING_FORMAT), $date->format(Date::FORMAT), $momentFormat, Date::getElapsedTime($date));
    }

    public static function manifestScript(string $name, array $attr = []): string {
        $url = Config::cdn() . '/' . Config::$a['manifest'][$name];
        $attribs = join(' ', array_map(function($v, $p) { return "$v=\"$p\""; }, array_keys($attr), $attr));
        $str = !empty($attribs) ? " $attribs" : "";
        return "<script$str src=\"$url\"></script>\r\n";
    }

    public static function manifestLink(string $name, array $attr = []): string {
        $url = Config::cdn() . '/' . Config::$a['manifest'][$name];
        $attr = array_merge(['rel' => 'stylesheet', 'media' => 'screen'], $attr);
        $attribs = join(' ', array_map(function($v, $p) { return "$v=\"$p\""; }, array_keys($attr), $attr));
        $str = !empty($attribs) ? " $attribs" : "";
        return "<link$str href=\"$url\">\r\n";
    }

    public static function ipLookupLink($ip): array {
        return array_map(function($n) use ($ip) {
            $url = str_replace('{IP_ADDRESS}', urlencode($ip), $n['url']);
            $n['link'] = $url;
            return $n;
        }, Config::$a['iplookupservices']);
    }

}