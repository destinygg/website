<?php
use Destiny\Common\Config;
use Doctrine\Common\Cache\RedisCache;

define ( '_BASEDIR', realpath ( __DIR__ . '/../../' ) );
$loader = require _BASEDIR . '/vendor/autoload.php';

Config::load ( array_replace_recursive (
    require _BASEDIR . '/config/config.php',
    require _BASEDIR . '/config/config.local.php',
    json_decode ( file_get_contents ( _BASEDIR . '/composer.json' ), true )
) );

$redis = new Redis ();
$redis->connect ( Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port'] );
$redis->select ( Config::$a ['redis'] ['database'] );

$cache = new RedisCache ();
$cache->setRedis ( $redis );
$cache->setNamespace( Config::$a['cache']['namespace'] );
$cache->deleteAll();

echo "Cache cleared." . PHP_EOL;
