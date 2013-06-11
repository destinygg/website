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
		'logPath' => Config::$a ['log'] ['path'],
		'schedule' => array (
				array (
						'action' => 'Recentgames',
						'lastExecuted' => null,
						'frequency' => 7,
						'period' => 'minute',
						'executeOnStart' => false 
				),
				array (
						'action' => 'Ingame',
						'lastExecuted' => null,
						'frequency' => 4,
						'period' => 'minute',
						'executeOnStart' => false 
				),
				array (
						'action' => 'Aggregate',
						'lastExecuted' => null,
						'frequency' => 5,
						'period' => 'minute',
						'executeOnStart' => false 
				),
				array (
						'action' => 'Freechamps',
						'lastExecuted' => null,
						'frequency' => 3,
						'period' => 'day',
						'executeOnStart' => false 
				),
				array (
						'action' => 'SubscriptionExpire',
						'lastExecuted' => null,
						'frequency' => 5,
						'period' => 'minute',
						'executeOnStart' => false 
				),
				array (
						'action' => 'LeagueStatus',
						'lastExecuted' => null,
						'frequency' => 2,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'LastFmFeed',
						'lastExecuted' => null,
						'frequency' => 1,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'YoutubeFeed',
						'lastExecuted' => null,
						'frequency' => 15,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'BroadcastsFeed',
						'lastExecuted' => null,
						'frequency' => 15,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'TwitterFeed',
						'lastExecuted' => null,
						'frequency' => 15,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'SummonersFeed',
						'lastExecuted' => null,
						'frequency' => 5,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'CalendarEvents',
						'lastExecuted' => null,
						'frequency' => 30,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'BlogFeed',
						'lastExecuted' => null,
						'frequency' => 30,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'StreamInfo',
						'lastExecuted' => null,
						'frequency' => 1,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'Champions',
						'lastExecuted' => null,
						'frequency' => 30,
						'period' => 'minute',
						'executeOnStart' => true 
				),
				array (
						'action' => 'Leaderboards',
						'lastExecuted' => null,
						'frequency' => 10,
						'period' => 'minute',
						'executeOnStart' => true 
				) 
		) 
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