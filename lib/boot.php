<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Destiny\Common\Config;
use Doctrine\DBAL\DriverManager;
use Doctrine\Common\Cache\RedisCache;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// This should be in the server config
ini_set ( 'date.timezone', 'UTC' );

// Used when the full path is needed to the base directory
define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );
$loader = require _BASEDIR . '/vendor/autoload.php';

Config::load ( array_replace_recursive ( 
    require _BASEDIR . '/config/config.php', 
    require _BASEDIR . '/config/config.local.php', 
    json_decode ( file_get_contents ( _BASEDIR . '/composer.json' ), true ) 
) );

AnnotationRegistry::registerLoader ( array ($loader, 'loadClass') );

$app = new Destiny\Common\Application ();
$app->setLoader ( $loader );

$log = new Logger ( $context->log );
$log->pushHandler ( new StreamHandler ( Config::$a ['log'] ['path'] . $context->log . '.log', Logger::INFO ) );
$log->pushProcessor ( new Monolog\Processor\WebProcessor () );
$app->setLogger ( $log );

$app->setConnection ( DriverManager::getConnection ( Config::$a ['db'], new Doctrine\DBAL\Configuration () ) );

$redis = new \Redis ();
$redis->connect ( Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port'] );
$redis->select ( Config::$a ['redis'] ['database'] );
$app->setRedis ( $redis );

$cache = new RedisCache ();
$cache->setRedis ( $app->getRedis () );
$app->setCacheDriver ( $cache );