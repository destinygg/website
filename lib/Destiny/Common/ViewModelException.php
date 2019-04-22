<?php
namespace Destiny\Common;

use Throwable;

class ViewModelException extends \Exception {

    public function __construct($message, Throwable $previous = null){
        parent::__construct($message, 0, $previous);
    }

}
