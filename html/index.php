<?php
use Destiny\Application;
use Destiny\SessionCookieInterface;
use Destiny\SessionInterface;
use Destiny\Session;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
define ( 'PP_CONFIG_PATH', $base . '/lib' ); // Paypal configuration
Config::load ( $base . '/lib/config.php' );

$log = new Logger ( 'events' );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . '/events.log', Logger::DEBUG ) );
$app = Application::getInstance ();
$app->setLogger ( $log );

Session::init ( Config::$a ['cookie'] );

// Simple HTTP request calls
$log->debug ( sprintf ( 'HTTP: %s', $app->getPath () ) );

// Admins only
$app->bind ( '/^\/(admin)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [admin] %s', $app->getPath () ) );
	if (! Session::authorized () || ! Session::hasRole ( 'admin' )) {
		$app->error ( 403 );
	}
} );

// Logged in only
$app->bind ( '/^\/(profile|order|subscribe)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [user] %s', $app->getPath () ) );
	if (! Session::authorized ()) {
		$app->error ( 401 );
	}
} );

// "Easy" way to invoke actions based on the URL, second param is the default action
$app->bindNamespace ( 'Destiny\Action', 'Home' );

// Nothing routed
$app->error ( 404 );
?>