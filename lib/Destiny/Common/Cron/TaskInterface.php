<?php
namespace Destiny\Common\Cron;

use Exception;

interface TaskInterface {

    /**
     * @return mixed|void
     *
     * @throws Exception
     */
    function execute();

}