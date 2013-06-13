<?php
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\SessionAuthenticationCredentials;
use Destiny\SessionCookieInterface;
use Destiny\SessionInstance;
use Destiny\Session;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
Config::load ( $base . '/lib/config.php' );

$log = new Logger ( 'http' );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . '/events.log', Logger::DEBUG ) );
$log->pushProcessor ( new WebProcessor () );

$app = Application::getInstance ();
$app->setLogger ( $log );

$session = Session::setInstance ( new SessionInstance () );
$session->setSessionCookieInterface ( new SessionCookieInterface ( Config::$a ['cookie'] ) );
$session->setAuthenticationCredentials ( new SessionAuthenticationCredentials () );
$session->start ();

// Admins only
$app->bind ( '/^\/(admin)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [admin] %s', $app->getPath () ) );
	if (! Session::authorized () || ! Session::hasRole ( 'admin' )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// Logged in only
$app->bind ( '/^\/(profile|order|subscribe|fantasy)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [user] %s', $app->getPath () ) );
	if (! Session::authorized ()) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// "Easy" way to invoke actions based on the URL, second param is the default action
$app->bindNamespace ( 'Destiny\Action', 'Home' );

// Nothing routed
$app->error ( Http::STATUS_NOT_FOUND );
?>