<?php
namespace Destiny\Common;

use Doctrine\DBAL\DBALException;

interface TaskInterface {

    /**
     * @return mixed
     *
     * @throws Exception
     * @throws DBALException
     */
    function execute();

}