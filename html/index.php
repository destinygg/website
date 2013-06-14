<?php
use Destiny\Utils\Http;
use Destiny\Application;
use Destiny\SessionAuthenticationCredentials;
use Destiny\SessionCookieInterface;
use Destiny\SessionInstance;
use Destiny\Session;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
Config::load ( $base . '/lib/config.php' );

$log = new \Monolog\Logger ( 'http' );
$log->pushHandler ( new \Monolog\Handler\StreamHandler ( Config::$a ['log'] ['path'] . '/events.log', \Monolog\Logger::DEBUG ) );
$log->pushProcessor ( new \Monolog\Processor\WebProcessor () );

$dbConfig = new \Doctrine\DBAL\Configuration ();
//$dbConfig->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
$db = \Doctrine\DBAL\DriverManager::getConnection ( Config::$a ['db'], $dbConfig );

$app = Application::instance ();
$app->setLogger ( $log );
$app->setConnection ( $db );

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