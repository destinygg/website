<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Exception;

/*
 * Thrown if the filter param is not validated
*/
class FilterParamsException extends Exception {}

/*
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
    public static function isRequired(array $params, $identifier) {
        if (! isset ( $params [$identifier] ) || empty ( $params [$identifier] ) || trim ( $params [$identifier] ) == '') {
            throw new FilterParamsException ( sprintf ( 'Required field missing %s', $identifier ) );
        }
    }
    
    /**
     * Make sure a parameter isset (had to use isThere, cause isset is a reserve word)
     * 
     * @param array $params
     * @param unknown $identifier
     * @throws FilterParamsException
     */
    public static function isThere(array $params, $identifier) {
        if (! isset ( $params [$identifier] )) {
            throw new FilterParamsException ( sprintf ( 'Field not set %s', $identifier ) );
        }
    }
}