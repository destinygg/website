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

// Remove annotation cache
$directory = realpath ( _BASEDIR . '/app/tmp/annotation' );
$log->info ( sprintf ( 'Deleting dir contents [%s]', $directory ) );
$objects = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $directory ), RecursiveIteratorIterator::LEAVES_ONLY );
$count = 0;
foreach ( $objects as $file => $object ) {
	unlink ( $file );
	++ $count;
}
$log->info ( sprintf ( 'Deleted [%s] files', $count ) );
$log->info ( 'Complete' );