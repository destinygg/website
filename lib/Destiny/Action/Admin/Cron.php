<?php

namespace Destiny\Action\Admin;

use Destiny\AppException;
use Destiny\Application;
use Destiny\Scheduler;
use Destiny\Utils\Http;
use Destiny\Config;
use Destiny\Mimetype;
use Psr\Log\LoggerInterface;

class Cron {

	public function execute(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new AppException ( 'Action id required.' );
		}
		set_time_limit ( 180 );
		$log = Application::getInstance ()->getLogger ();
		
		$response = array ();
		$scheduler = new Scheduler ( Config::$a ['scheduler'] );
		$scheduler->setLogger ( $log );
		$scheduler->loadSchedule ();
		$scheduler->executeTaskByName ( $params ['id'] );
		$response ['message'] = sprintf ( 'Execute %s', $params ['id'] );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}