<?php
use Destiny\Utils\Http;

use Destiny\Application;
use Destiny\AppException;
use Destiny\Session;
use Destiny\Scheduler;
use Destiny\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
Config::load ( $base . '/config/config.php', parse_ini_file ( $base . '/lib/.version' ) );

$log = new Logger ( 'cron' );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . 'cron.log', Logger::DEBUG ) );
$log->pushProcessor ( new WebProcessor () );
$log->pushProcessor ( new ProcessIdProcessor () );
$log->pushProcessor ( new MemoryPeakUsageProcessor () );

$db = \Doctrine\DBAL\DriverManager::getConnection ( Config::$a ['db'], new \Doctrine\DBAL\Configuration () );
$cache = new \Doctrine\Common\Cache\ApcCache ();

$app = Application::instance ();
$app->setLogger ( $log );
$app->setConnection ( $db );
$app->setCacheDriver ( $cache );

try {
	if (! isset ( $_REQUEST ['action'] ) || empty ( $_REQUEST ['action'] )) {
		throw new AppException ( 'Action required' );
	}
	if (! isset ( $_REQUEST ['token'] ) || empty ( $_REQUEST ['token'] )) {
		throw new AppException ( 'Token required' );
	}
	$stmt = $db->prepare ( '
		SELECT COUNT(*) FROM dfl_scheduled_tasks_proxy 
		WHERE taskName = :taskName AND taskToken = :taskToken
		LIMIT 0,1
	' );
	$stmt->bindValue ( 'taskName', $_REQUEST ['action'] );
	$stmt->bindValue ( 'taskToken', $_REQUEST ['token'] );
	$stmt->execute ();
	if (intval ( $stmt->fetchColumn () ) === 1) {
		$db->delete ( 'dfl_scheduled_tasks_proxy', array (
				'taskName' => $_REQUEST ['action'],
				'taskToken' => $_REQUEST ['token'] 
		) );
		$scheduler = new Scheduler ( Config::$a ['scheduler'] );
		$scheduler->setLogger ( $log );
		$scheduler->loadSchedule ();
		$task = $scheduler->getTaskByName ( $_REQUEST ['action'] );
		if (empty ( $task )) {
			throw new AppException ( 'Invalid action' );
		}
		$scheduler->executeTask ( $task );
	}
	throw new AppException ( 'Not a valid key' );
} catch ( \Exception $e ) {
	$log->error ( $e->getMessage () );
	Http::status ( Http::STATUS_ERROR );
	exit ();
}