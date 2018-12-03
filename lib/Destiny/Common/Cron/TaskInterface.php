<?php
namespace Destiny\Common\Cron;

use Destiny\Common\Exception;
use Doctrine\DBAL\DBALException;

interface TaskInterface {

    /**
     * @return mixed|void
     *
     * @throws Exception
     * @throws DBALException
     */
    function execute();

}