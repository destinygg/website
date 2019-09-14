<?php
namespace Destiny\Common\Cron;

use Destiny\Common\Exception;

interface TaskInterface {

    /**
     * @return mixed|void
     *
     * @throws Exception
     */
    function execute();

}