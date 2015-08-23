<?php
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Scheduler;
use Destiny\Common\Config;

ini_set ( 'mysql.connect_timeout', 10 );
ini_set ( 'max_execution_time', 60 );

$context = new \stdClass();
$context->log = 'cron';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Cron is run every 60 seconds.
// There can be a time where actions are executed before they have ended
$log = $app->getLogger ();
$scheduler = new Scheduler ( Config::$a ['scheduler'] );
$scheduler->setLogger ( $log );
$scheduler->loadSchedule ();
$stime = microtime ( true );
try {
	echo PHP_EOL . 'Scheduler starting';
	$scheduler->executeSchedule ( false );
	echo PHP_EOL . 'Scheduler completed';
} catch ( Exception $e ) {
	$log->error ( $e->getMessage () );
	echo PHP_EOL . 'Scheduler completed with errors';
} catch ( \Exception $e ) {
	$log->critical ( $e->getMessage () );
	echo PHP_EOL . 'Scheduler completed with errors';
}
echo PHP_EOL . 'Completed in ' . (microtime ( true ) - $stime) . ' seconds';
echo PHP_EOL;
?>