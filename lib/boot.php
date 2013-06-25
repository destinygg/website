<?php
// Used when the full path is needed to the base directory
define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( '_VENDORDIR', _BASEDIR . '/vendor' );
// Paypal configuration
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );
require _VENDORDIR . '/autoload.php';

\Destiny\Config::load ( _BASEDIR . '/config/config.php', _BASEDIR . '/lib/.version' );

$log = new \Monolog\Logger ( $context->log );
$log->pushHandler ( new \Monolog\Handler\StreamHandler ( \Destiny\Config::$a ['log'] ['path'] . $context->log . '.log', \Monolog\Logger::INFO ) );
$log->pushProcessor ( new \Monolog\Processor\WebProcessor () );
$log->pushProcessor ( new \Monolog\Processor\ProcessIdProcessor () );
$log->pushProcessor ( new \Monolog\Processor\MemoryPeakUsageProcessor () );

$db = \Doctrine\DBAL\DriverManager::getConnection ( \Destiny\Config::$a ['db'], new \Doctrine\DBAL\Configuration () );
$db->exec ( 'SET NAMES utf8' );
$db->exec ( 'SET CHARACTER SET utf8' );
$db->exec ( 'SET time_zone = \'+00:00\'' );

$app = new \Destiny\Application ();

$redis = 'Redis';
if (class_exists ( $redis )) {
	$redis = new $redis ();
	$redis->connect ( \Destiny\Config::$a ['redis'] ['host'], \Destiny\Config::$a ['redis'] ['port'] );
	$app->setRedis ( $redis );
	$cache = new \Doctrine\Common\Cache\RedisCache ();
	$cache->setRedis ( $app->getRedis () );
} else {
	$cache = new \Doctrine\Common\Cache\FilesystemCache ( \Destiny\Config::$a ['cache'] ['path'] );
}

$app->setLogger ( $log );
$app->setConnection ( $db );
$app->setCacheDriver ( $cache );