<?php
use Destiny\Common\Config;

// Used when the full path is needed to the base directory
define ( '_BASEDIR', realpath ( __DIR__ . '/../' ) );
define ( 'PP_CONFIG_PATH', _BASEDIR . '/config/' );
$loader = require _BASEDIR . '/vendor/autoload.php';

Config::load ( array_replace_recursive ( 
  require _BASEDIR . '/config/config.php', 
  require _BASEDIR . '/config/config.local.php', 
  json_decode ( file_get_contents ( _BASEDIR . '/composer.json' ), true ) 
) );

return $loader;