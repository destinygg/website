<?php
namespace Destiny\Common;

use function get_called_class;

abstract class Service {

    /** @var Service[] */
    protected static $_instances = [];

    public static function instance() {
        $class = get_called_class();
        if ( !isset( static::$_instances[$class] ) ){
            static::$_instances[$class] = new static;
        }
        return static::$_instances[$class];
    }

}