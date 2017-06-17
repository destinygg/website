<?php
namespace Destiny\Common;

class ViewModelException extends \Exception {

    public function __construct($message, \Throwable $previous = null){
        parent::__construct($message, 0, $previous);
    }

}
