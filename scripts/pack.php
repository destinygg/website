<?php
if ( !defined( '_BASEDIR') )
	define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( '_VENDORDIR', _BASEDIR . '/vendor' );
define ( '_STATICDIR', _BASEDIR . '/static' );

require _VENDORDIR . '/autoload.php';
require 'include/FileUtils.php';

$stream = new \Monolog\Handler\StreamHandler ( 'php://stdout', \Monolog\Logger::DEBUG );
$stream->setFormatter ( new \Monolog\Formatter\LineFormatter ( "%level_name% %message%\n", "H:i:s" ) );
$log = new \Monolog\Logger ( 'DEBUG' );
$log->pushHandler ( $stream );

FileUtils::$b = _STATICDIR;
FileUtils::$log = $log;

$log->info ( sprintf ( 'Starting with base [%s]', _STATICDIR ) );

// Errors concat and compress
FileUtils::delete ( '/errors/css/style.min.css' );
FileUtils::copy ( '/errors/css/style.css', '/errors/css/style.min.css' );
FileUtils::compress ( '/errors/css/style.min.css' );

// Chat CSS
FileUtils::delete ( '/chat/css/style.min.css' );
FileUtils::concat ( '/chat/css/style.min.css', array (
	'/chat/css/style.css',
	'/chat/css/emoticons.css',
	'/chat/css/flair.css'
) );
FileUtils::compress ( '/chat/css/style.min.css' );


// Chat JS
FileUtils::delete ( '/chat/js/engine.min.js' );
FileUtils::concat ( '/chat/js/engine.min.js', array (
	'/chat/js/autocomplete.js',
	'/chat/js/scroll.mCustom.js',
	'/chat/js/chat.menu.js',
	'/chat/js/formatters.js',
	'/chat/js/hints.js',
	'/chat/js/gui.js',
	'/chat/js/chat.js' 
) );
FileUtils::compress ( '/chat/js/engine.min.js' );

// Web CSS
FileUtils::delete ( '/web/css/style.min.css' );
FileUtils::concat ( '/web/css/style.min.css', array (
	'/web/css/style.css',
	'/web/css/flags.css'
) );
FileUtils::compress ( '/web/css/style.min.css' );

// Web JS
FileUtils::delete ( '/web/js/destiny.min.js' );
FileUtils::concat ( '/web/js/destiny.min.js', array (
	'/web/js/utils.js',
	'/web/js/destiny.js',
	'/web/js/feed.js',
	'/web/js/twitch.js',
	'/web/js/ui.js' 
) );
FileUtils::compress ( '/web/js/destiny.min.js' );

$log->info ( 'Complete' );