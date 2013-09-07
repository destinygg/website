<?php
namespace Destiny\Action\Admin;

use Destiny\Common\Exception;
use Destiny\Common\Application;
use Destiny\Common\Scheduler;
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\Annotation\Action;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Psr\Log\LoggerInterface;

/**
 * @Action
 */
class Cron {

	/**
	 * @Route ("/admin/cron")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params
	 * @throws Exception
	 */
	public function execute(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new Exception ( 'Action id required.' );
		}
		set_time_limit ( 180 );
		$log = Application::instance ()->getLogger ();
		
		$response = array ();
		$scheduler = new Scheduler ( Config::$a ['scheduler'] );
		$scheduler->setLogger ( $log );
		$scheduler->loadSchedule ();
		$scheduler->executeTaskByName ( $params ['id'] );
		$response ['message'] = sprintf ( 'Execute %s', $params ['id'] );
		Http::header ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		Http::sendString ( json_encode ( $response ) );
	}

}