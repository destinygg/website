<?php
use Destiny\Application;
use Destiny\AppException;
use Destiny\Session;
use Destiny\Scheduler;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
Config::load ( $base . '/config/config.php', parse_ini_file ( $base . '/lib/.version' ) );

$log = new \Monolog\Logger ( 'cron' );
$log->pushHandler ( new \Monolog\Handler\StreamHandler ( Config::$a ['log'] ['path'] . 'cron.log', \Monolog\Logger::DEBUG ) );
$log->pushProcessor ( new \Monolog\Processor\WebProcessor () );
$log->pushProcessor ( new \Monolog\Processor\ProcessIdProcessor () );
$log->pushProcessor ( new \Monolog\Processor\MemoryPeakUsageProcessor () );

$db = \Doctrine\DBAL\DriverManager::getConnection ( Config::$a ['db'], new \Doctrine\DBAL\Configuration () );
$cache = new \Doctrine\Common\Cache\ApcCache ();

$app = Application::instance ();
$app->setLogger ( $log );
$app->setConnection ( $db );
$app->setCacheDriver ( $cache );

// Cron is run every 60 seconds.
// There can be a time where actions are executed before they have ended
$scheduler = new Scheduler ( Config::$a ['scheduler'] );
$scheduler->setLogger ( $log );
$scheduler->loadSchedule ();
$stime = microtime ( true );

try {
	$scheduler->executeShedule ( true );
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