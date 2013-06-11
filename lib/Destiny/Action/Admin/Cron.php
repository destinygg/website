<?php

namespace Destiny\Action\Admin;

use Destiny\Application;
use Destiny\Scheduler;
use Destiny\Utils\Http;
use Destiny\Config;
use Destiny\Mimetype;
use Psr\Log\LoggerInterface;

class Cron {

	public function execute(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new \Exception ( 'Action id required.' );
		}
		set_time_limit ( 180 );
		$log = Application::getInstance ()->getLogger ();
		$scheduler = new Scheduler ( array (
				'logger' => $log,
				'logPath' => Config::$a ['log'] ['path'] 
		) );
		$response = array ();
		try {
			$scheduler->executeAction ( $params ['id'] );
			$response ['message'] = sprintf ( 'Execute [%s]', $params ['id'] );
		} catch ( \Exception $e ) {
			$log->error ( $e->getMessage () );
			$response ['message'] = $e->getMessage ();
		}
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}