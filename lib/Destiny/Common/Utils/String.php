<?php
namespace Destiny\Common\Utils;

use Destiny\Common\Utils\String\Params;

/**
 * Simple parameterized string utility
 */
class String {
    
    protected $value = '';
    protected $params = array ();

    public function __construct($value, array $args = null) {
        if (is_string ( $value )) {
            $this->value = $value;
            $this->params = $args;
        }
        Options::setOptions ( $this, $args );
    }

    public function __toString() {
        return Params::apply ( $this->value, $this->params );
    }

}