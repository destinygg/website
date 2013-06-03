<?php
namespace Destiny\Action\Admin;

use Destiny\Logger;
use Destiny\Utils\Http;
use Destiny\Config;
use Destiny\Mimetype;

class Cron {

	public function execute(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new \Exception ( 'Action id required.' );
		}
		set_time_limit(180);
		$response = array ();
		$actionClass = 'Destiny\\Cron\\' . $params ['id'];
		$log = new Logger ( Config::$a ['log'] ['path'] . strtolower ( $params ['id'] ) . '.log' );
		$log->log ( 'Admin execute start' );
		try {
			if (class_exists ( $actionClass, true )) {
				$actionObj = new $actionClass ();
				$actionObj->execute ( $log );
				$response ['message'] = 'Executed action: "' . $params ['id'] . '"';
			}else{
				$response ['message'] = 'Action not found: "' . $params ['id'] . '"';
			}
		} catch ( \Exception $e ) {
			$log->log ( $e->getMessage () );
			$response ['message'] = $e->getMessage ();
		}
		$log->log ( 'Admin execute end' );
		Http::header ( Http::HEADER_CONTENTTYPE, Mimetype::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}