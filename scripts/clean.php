<?php
define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( '_VENDORDIR', _BASEDIR . '/vendor' );
define ( '_STATICDIR', _BASEDIR . '/static' );
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );

require _VENDORDIR . '/autoload.php';
require 'include/FileUtils.php';

$stream = new \Monolog\Handler\StreamHandler ( 'php://stdout', \Monolog\Logger::DEBUG );
$stream->setFormatter ( new \Monolog\Formatter\LineFormatter ( "%level_name% %message%\n", "H:i:s" ) );
$log = new \Monolog\Logger ( 'DEBUG' );
$log->pushHandler ( $stream );

FileUtils::$b = _STATICDIR;
FileUtils::$log = $log;

$log->info ( sprintf ( 'Starting with base [%s]', _STATICDIR ) );

FileUtils::delete ( '/errors/css/style.min.css' );
FileUtils::delete ( '/chat/css/style.min.css' );
FileUtils::delete ( '/chat/js/engine.min.js' );
FileUtils::delete ( '/web/css/style.min.css' );
FileUtils::delete ( '/web/js/destiny.min.js' );