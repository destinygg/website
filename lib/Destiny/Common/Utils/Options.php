<?php
namespace Destiny\Common\Utils;

/**
 * Abstract class to help set class properties using constructor arguments
 */
abstract class Options {

    /**
     * @param mixed $object
     * @param array|null $options
     * @return bool
     */
    public static function setOptions($object, array $options = null) {
        if (is_array ( $options )) {
            foreach ( $options as $key => $value ) {
                $method = 'set' . $key;
                if (method_exists ( $object, $method )) {
                    $object->$method ( $value );
                } else if (property_exists ( $object, $key )) {
                    $object->$key = $value;
                }
            }
        }
        return true;
    }

}