<?php
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Scheduler;
use Destiny\Common\Config;

require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

$log = $app->getLogger ();
$scheduler = new Scheduler ( Config::$a ['scheduler'] );
$scheduler->setLogger ( $log );
$scheduler->loadSchedule ();
$stime = microtime ( true );
try {
	echo PHP_EOL . 'Scheduler starting';
	$scheduler->executeSchedule ();
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