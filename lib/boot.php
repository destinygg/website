<?php
use Destiny\Common\Config;
use Destiny\Common\Application;
use Doctrine\DBAL\DriverManager;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use \Redis;

ini_set ( 'date.timezone', 'UTC' );

// Used when the full path is needed to the base directory
define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( '_LIBDIR', _BASEDIR . '/lib' );
define ( '_VENDORDIR', _BASEDIR . '/vendor' );
define ( '_STATICDIR', _BASEDIR . '/static' );
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );
$loader = require _VENDORDIR . '/autoload.php';

AnnotationRegistry::registerLoader ( array (
	$loader,
	'loadClass' 
) );

Config::load ( array_replace_recursive ( 
	require _BASEDIR . '/config/config.php', 
	require _BASEDIR . '/config/config.local.php', 
	json_decode ( file_get_contents ( _BASEDIR . '/composer.json' ), true ) 
) );

$app = new Application ();
$app->setLoader ( $loader );

$log = new Logger ( $context->log );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . $context->log . '.log', Logger::INFO ) );
$log->pushProcessor ( new WebProcessor () );
$log->pushProcessor ( new MemoryPeakUsageProcessor () );
$app->setLogger ( $log );

$db = DriverManager::getConnection ( Config::$a ['db'], new Configuration () );
$db->exec ( 'SET NAMES utf8' );
$db->exec ( 'SET CHARACTER SET utf8' );
$db->exec ( 'SET time_zone = \'+00:00\'' );
$app->setConnection ( $db );

if (class_exists ( 'Redis' )) {
	$redis = new Redis ();
	$redis->connect ( Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port'] );
	$redis->select ( Config::$a ['redis'] ['database'] );
	$app->setRedis ( $redis );
	$cache = new RedisCache ();
	$cache->setRedis ( $app->getRedis () );
} else {
	$cache = new FilesystemCache ( Config::$a ['cache'] ['path'] );
}
$app->setCacheDriver ( $cache );
?>