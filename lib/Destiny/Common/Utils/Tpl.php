<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Config;
use Misd\Linkify\Linkify;

class Tpl {
    
    public static function file($filename){
        return Config::$a['tpl']['path'] . $filename;
    }

    public static function jsout($var) {
        return json_encode ( $var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    }

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

    public static function title($title) {
        $str = Config::$a ['meta'] ['title'];
        if (! empty ( $title )) {
            $str = sprintf ( '%s - %s', $title, Config::$a ['meta'] ['shortName'] );
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

    public static function formatTextForDisplay($text) {
        $text = self::out($text);
        $linkify = new Linkify();
        $text = $linkify->process($text, array('attr'=>array('target'=>'_blank')));
        $text = self::emotify($text);
        return $text;
    }

    public static function emotify($text) {
        $emotes = Config::$a ['chat'] ['customemotes'];
        $lcemotes = array_map('strtolower', $emotes);
        $pattern = '/(^|[\s,\.\?!])('. join($emotes, '|') .')(?=$|[\s,\.\?!])/i';
        $callback = function ($match) use ($emotes, $lcemotes) {
            return '<i class="chat-emote chat-emote-' . $emotes[array_search(strtolower($match[2]), $lcemotes)] . '"></i>';
        };
        $chunks = preg_split('/(<.+?>)/is', $text, 0, PREG_SPLIT_DELIM_CAPTURE);
        $openTag = null;
        for ($i = 0; $i < count($chunks); $i++) {
            if ($i % 2 === 0) {
                if ($openTag === null)
                    $chunks[$i] = preg_replace_callback($pattern, $callback, $chunks[$i]);
            } else {
                if ($openTag === null && preg_match("`<(.+?).*(?<!/)>$`is", $chunks[$i], $matches))
                    $openTag = $matches[1];
                else if (preg_match('`</\s*' . $openTag . '>`i', $chunks[$i], $matches))
                    $openTag = null;
            }
        }
        return implode($chunks);
    }

}