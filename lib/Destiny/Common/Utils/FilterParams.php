<?php
namespace Destiny\Common\Utils;

/**
 * Abstract class to help with handling request variables
 */
class FilterParams {

    /**
     * Make sure a parameter is set and not empty
     * Does a white-space trim
     * @throws FilterParamsException
     */
    public static function required(array $params, string $identifier = '') {
        if (self::isEmpty($params, $identifier)) {
            throw new FilterParamsException (sprintf('Required field missing %s', $identifier));
        }
    }

    /**
     * Make sure a parameter has been declared
     * @throws FilterParamsException
     */
    public static function declared(array $params, string $identifier = '') {
        if (!is_array($params) || !isset ($params[$identifier])) {
            throw new FilterParamsException (sprintf('Field not set %s', $identifier));
        }
    }

    /**
     * Make sure a parameter has been declared and is an array
     * @throws FilterParamsException
     */
    public static function requireArray(array $params, string $identifier = '') {
        if (!is_array($params) || !isset ($params[$identifier]) || !is_array($params[$identifier])) {
            throw new FilterParamsException (sprintf('Field not set or not an array %s', $identifier));
        }
    }

    /**
     * Make sure the parameter is set and not empty
     */
    public static function isEmpty(array $params, string $identifier = ''): bool {
        if (is_array($params) && isset($params[$identifier])) {
            if (is_array($params[$identifier]) && sizeof($params[$identifier]) === 0)
                return true;
            if (is_string($params[$identifier]) && trim($params[$identifier]) == '')
                return true;
            if (empty($params[$identifier]))
                return true;
            return false;
        }
        return true;
    }

    /**
     * Make sure the parameter is set and an array
     */
    public static function isArray(array $params, string $identifier = ''): bool {
        return !(!is_array($params) || !isset($params[$identifier]) || !is_array($params[$identifier]));
    }
}
