<?php
use Destiny\Application;
use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\SessionAuthenticationCredentials;
use Destiny\SessionCookieInterface;
use Destiny\SessionInstance;
use Destiny\Session;
use Destiny\Config;

$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );
Config::load ( $base . '/config/config.php', parse_ini_file ( $base . '/lib/.version' ) );

$log = new \Monolog\Logger ( 'http' );
$log->pushHandler ( new \Monolog\Handler\StreamHandler ( Config::$a ['log'] ['path'] . 'events.log', \Monolog\Logger::DEBUG ) );
$log->pushProcessor ( new \Monolog\Processor\WebProcessor () );

$db = \Doctrine\DBAL\DriverManager::getConnection ( Config::$a ['db'], new \Doctrine\DBAL\Configuration () );
$cache = new \Doctrine\Common\Cache\FilesystemCache ( Config::$a ['cache'] ['path'] );

$app = Application::instance ();
$app->setLogger ( $log );
$app->setConnection ( $db );
$app->setCacheDriver ( $cache );

$session = Session::setInstance ( new SessionInstance () );
$session->setSessionCookieInterface ( new SessionCookieInterface ( Config::$a ['cookie'] ) );
$session->start ();
$session->setAuthenticationCredentials ( new SessionAuthenticationCredentials ( $session->getData () ) );

// Admins only
$app->bind ( '/^\/(admin|order|subscribe|payment)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [admin] %s', $app->getPath () ) );
	if (! Session::hasRole ( 'user' ) || ! Session::hasRole ( 'admin' )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// Logged in only
$app->bind ( '/^\/(profile|order|subscribe|fantasy|payment|bigscreen)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [user] %s', $app->getPath () ) );
	if (! Session::hasRole ( 'user' )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// "Easy" way to invoke actions based on the URL, second param is the default action
$app->bindNamespace ( 'Destiny\Action', 'Home' );

// Nothing routed
$app->error ( Http::STATUS_NOT_FOUND );
?>