<?php
use Destiny\Application;
use Destiny\AppException;
use Destiny\Session;
use Destiny\Scheduler;
use Destiny\Config;

$context->log = 'cron';
require __DIR__ . '/../lib/boot.php';

$app = Application::instance ();
$log = $app->getLogger ();

// Cron is run every 60 seconds.
// There can be a time where actions are executed before they have ended
$scheduler = new Scheduler ( Config::$a ['scheduler'] );
$scheduler->setLogger ( $log );
$scheduler->loadSchedule ();
$stime = microtime ( true );
try {
	echo PHP_EOL . 'Scheduler starting';
	$scheduler->executeShedule ( false );
	echo PHP_EOL . 'Scheduler completed';
} catch ( AppException $e ) {
	$log->error ( $e->getMessage () );
	echo PHP_EOL . 'Scheduler completed with errors';
} catch ( \Exception $e ) {
	$log->critical ( $e->getMessage () );
	echo PHP_EOL . 'Scheduler completed with errors';
}
echo PHP_EOL . 'Completed in ' . (microtime ( true ) - $stime) . ' seconds';
echo PHP_EOL;
?>