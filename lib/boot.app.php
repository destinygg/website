<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

define('_APP_VERSION', '2.3.25'); // auto-generated: 1538485092119
define('_BASEDIR', realpath(__DIR__ . '/../'));

$loader = require _BASEDIR . '/vendor/autoload.php';
Config::load(array_replace_recursive(
    require _BASEDIR . '/config/config.php',
    require _BASEDIR . '/config/config.dgg.php',
    require _BASEDIR . '/config/config.local.php',
    ['version' => _APP_VERSION]
));

set_include_path(get_include_path() . PATH_SEPARATOR . _BASEDIR . '/views/');

// Required to auto-load custom annotations
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

// Auto loader
$app = Application::instance();
$app->setLoader($loader);

// Logging
Log::$log = new Logger ('web');
Log::$log->pushHandler(new StreamHandler (_BASEDIR . '/log/web.log', Logger::ERROR));
Log::$log->pushProcessor(new PsrLogMessageProcessor());
Log::$log->pushProcessor(new Monolog\Processor\WebProcessor());

// Database
try {
    $app->setDbal(DriverManager::getConnection(Config::$a['db']));
} catch (\Exception $e) {
    Log::error("Could not setup DB connection. " . $e->getMessage());
    exit(1);
}

// Global Redis instance
$redis1 = new Redis();
$redis1->connect(Config::$a['redis']['host'], Config::$a['redis']['port']);
$redis1->select(Config::$a['redis']['database']);
$redis1->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
$app->setRedis($redis1);

// Cache implementation, with separate redis instance
$redis2 = new Redis();
$redis2->connect(Config::$a['redis']['host'], Config::$a['redis']['port']);
$redis2->select(Config::$a['redis']['database']);

$cache = new RedisCache();
$cache->setRedis($redis2);
$cache->setNamespace(Config::$a['cacheNamespace']);
$app->setCache($cache);