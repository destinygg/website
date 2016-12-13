<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ini_set ( 'date.timezone', 'UTC' );

define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );
$loader = require _BASEDIR . '/vendor/autoload.php';

Config::load ( array_replace_recursive (
    require _BASEDIR . '/config/config.php',
    require _BASEDIR . '/config/config.local.php',
    json_decode ( file_get_contents ( _BASEDIR . '/package.json' ), true )
) );
set_include_path(get_include_path() . PATH_SEPARATOR . Config::$a['tpl']['path']);

$app = Application::instance();
$app->setLoader ( $loader );

$log = new Logger('web');
$log->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));
$app->setLogger( $log );

$app->setConnection ( DriverManager::getConnection ( Config::$a ['db'] ) );
$app->setCacheDriver ( new Doctrine\Common\Cache\ArrayCache() );
