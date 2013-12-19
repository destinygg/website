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
		if (! isset ( $params [$identifier] ) || trim ( $params [$identifier] ) == '') {
			throw new FilterParamsException ( sprintf ( 'Required field missing %s', $identifier ) );
		}
	}
}