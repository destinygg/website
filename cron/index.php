<?php
use Destiny\Application;
use Destiny\Session;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Destiny\Scheduler;
use Destiny\Service\Leagueapi;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
define ( 'PP_CONFIG_PATH', $base . '/lib' ); // Paypal configuration
Config::load ( $base . '/lib/config.php' );

$log = new Logger ( 'events' );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . '/cron.log', Logger::DEBUG ) );
$app = Application::getInstance ();
$app->setLogger ( $log );

// Cron doesnt need session - this file should also not be accessible via browser
// Session::init ();

/**
 * Cron is run every 60 seconds.
 * There can be a time where actions are executed before they have ended
 */
$stime = microtime ( true );
$scheduler = new Scheduler ( array (
		'logger' => $log,
		'frequency' => 1,
		'period' => 'minute',
		'schedule' => Config::$a ['schedule'] 
) );

// If this is run via cron, the first argv is the file, the others are params
// If we are running it via http, simulate argv parameters by setting the first arg to the file, and second to ACTION
if (! isset ( $argv ) || ! is_array ( $argv )) {
	$argv = array ();
	$argv [] = __FILE__;
	if (isset ( $_GET ['action'] )) {
		$argv [] = $_GET ['action'];
	}
}
array_shift ( $argv );

$scheduler->execute ( $argv );

$log->info ( 'Completed cron: ' . (microtime ( true ) - $stime) . ' seconds' );
echo PHP_EOL . 'Completed scheduler: ' . (microtime ( true ) - $stime) . ' seconds' . PHP_EOL;
?>