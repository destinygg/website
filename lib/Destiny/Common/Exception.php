<?php
namespace Destiny\Common;

class Exception extends \Exception {

    public function __construct($message = "", \Exception $previous = null) {
        parent::__construct($message, 0, $previous);
    }

}