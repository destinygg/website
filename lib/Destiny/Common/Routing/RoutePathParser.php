<?php
namespace Destiny\Common\Routing;

class RoutePathParser {

    public static function search($pattern, $string) {
        $params = [];
        if (self::match($pattern, $string)) {
            $keys = self::getKeys($pattern);
            $values = self::getValues($pattern, $string);
            if (count($values) == count($keys)) {
                for ($i = 0; $i < count($keys); ++$i) {
                    $key = self::getKey($keys [$i]);
                    $params [self::getKeyName($key)] = self::getKeyValue($key, $values [$i]);
                }
            }
        }
        return $params;
    }

    public static function match($pattern, $string) {
        return (preg_match(self::getSearchString($pattern), $string) > 0);
    }

    protected static function getSearchString($pattern) {
        $find = ['/{[^}]*}/'];
        $replace = ['([A-z0-9\_\-\|\.]+)'];
        $subject = str_replace(['/', '.'], ['\\/', '\\.'], $pattern);
        return '/^' . preg_replace($find, $replace, $subject) . '$/i';
    }

    protected static function getKeys($pattern) {
        preg_match_all("/{[^}]*}/", $pattern, $keys, PREG_PATTERN_ORDER);
        if (is_array($keys [0])) {
            $keys = $keys [0];
        }
        return $keys;
    }

    protected static function getValues($pattern, $string) {
        preg_match_all(self::getSearchString($pattern), $string, $values, PREG_SET_ORDER);
        if (is_array($values [0])) {
            array_shift($values [0]);
            $values = $values [0];
        }
        return $values;
    }

    protected static function getKey($key) {
        return substr($key, 1, strlen($key) - 2);
    }

    protected static function getKeyName($key) {
        $pos = strpos($key, ':');
        return ($pos !== false && $pos > 0) ? substr($key, $pos + 1) : $key;
    }

    protected static function getKeyType($key) {
        $pos = strpos($key, ':');
        return ($pos !== false && $pos > 0) ? substr($key, 0, $pos) : null;
    }

    public static function getKeyValue($key, $value) {
        switch (self::getKeyType($key)) {
            case 'int' :
                $value = intval($value);
                break;
            default :
                $value = (string)$value;
                break;
        }
        return $value;
    }

}