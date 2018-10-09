<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Exception;

/**
 * Thrown if the filter param is not validated
*/
class FilterParamsException extends Exception {}

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
        if (!is_array($params) || ! isset ( $params [$identifier] ) || empty ( $params [$identifier] ) || trim ( $params [$identifier] ) == '') {
            throw new FilterParamsException ( sprintf ( 'Required field missing %s', $identifier ) );
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
        if (!is_array($params) || ! isset ( $params [$identifier] )) {
            throw new FilterParamsException ( sprintf ( 'Field not set %s', $identifier ) );
        }
    }
    
    /**
     * Make sure a parameter has been declared and is an array
     * 
     * @param array $params
     * @param string $identifier
     * @throws FilterParamsException
     */
    public static function isarray(array $params, $identifier) {
        if (!is_array($params) || ! isset ( $params [$identifier] ) || !is_array($params [$identifier])) {
            throw new FilterParamsException ( sprintf ( 'Field not set or not an array %s', $identifier ) );
        }
    }
}