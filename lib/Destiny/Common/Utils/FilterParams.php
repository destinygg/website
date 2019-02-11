<?php
namespace Destiny\Common\Utils;

/**
 * Abstract class to help with handling request variables
 */
abstract class FilterParams {

    /**
     * Make sure a parameter is set and not empty
     * Does a white-space trim
     *
     * @param array $params
     * @param string $identifier
     * @throws FilterParamsException
     */
    public static function required(array $params, $identifier) {
        if (self::isEmpty($params, $identifier)) {
            throw new FilterParamsException (sprintf('Required field missing %s', $identifier));
        }
    }

    /**
     * Make sure a parameter has been declared
     *
     * @param array $params
     * @param string $identifier
     * @throws FilterParamsException
     */
    public static function declared(array $params, $identifier) {
        if (!is_array($params) || !isset ($params [$identifier])) {
            throw new FilterParamsException (sprintf('Field not set %s', $identifier));
        }
    }

    /**
     * Make sure a parameter has been declared and is an array
     *
     * @param array $params
     * @param string $identifier
     * @throws FilterParamsException
     */
    public static function requireArray(array $params, $identifier) {
        if (!is_array($params) || !isset ($params [$identifier]) || !is_array($params [$identifier])) {
            throw new FilterParamsException (sprintf('Field not set or not an array %s', $identifier));
        }
    }

    /**
     * Make sure the parameter is set and not empty
     *
     * @param array $params
     * @param $identifier
     * @return bool
     */
    public static function isEmpty(array $params, $identifier) {
        if (!is_array($params) || !isset ($params [$identifier]) || strlen ($params [$identifier]) == 0 || trim($params [$identifier]) == '') {
            return true;
        }
        return false;
    }

    /**
     * Make sure the parameter is set and an array
     *
     * @param array $params
     * @param $identifier
     * @return bool
     */
    public static function isArray(array $params, $identifier) {
        if (!is_array($params) || !isset ($params [$identifier]) || !is_array($params [$identifier])) {
            return false;
        }
        return true;
    }
}
