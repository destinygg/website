<?php
namespace Destiny\Tasks;

use Destiny\Common\Annotation\Schedule;
use Destiny\Common\Authentication\RememberMeService;
use Destiny\Common\TaskInterface;

/**
 * @Schedule(frequency=1,period="hour")
 */
class RememberMe implements TaskInterface {


    function execute() {
        RememberMeService::instance ()->clearExpiredRememberMe ();
    }
}