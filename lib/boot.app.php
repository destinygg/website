<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ini_set('date.timezone', 'UTC'); // This should be in the server config
define('_APP_VERSION', '2.0.75'); // auto-generated: 1497453147720
define('_BASEDIR', realpath(__DIR__ . '/../'));

$loader = require _BASEDIR . '/vendor/autoload.php';
Config::load(array_replace_recursive(
    require _BASEDIR . '/config/config.php',
    require _BASEDIR . '/config/config.local.php',
    ['version' => _APP_VERSION]
));

set_include_path(get_include_path() . PATH_SEPARATOR . Config::$a['tpl']['path']);

// Required to auto-load custom annotations
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = Application::instance();
$app->setLoader($loader);

$logger = new Logger ('web');
$logger->pushHandler(new StreamHandler (Config::$a ['log'] ['path'] . 'web.log', Logger::ERROR));
$logger->pushProcessor(new Monolog\Processor\WebProcessor ());
$app->setLogger($logger);

$app->setConnection(DriverManager::getConnection(Config::$a ['db']));

$redis = new Redis ();
$redis->connect(Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port']);
$redis->select(Config::$a ['redis'] ['database']);
$app->setRedis($redis);

$cache = new RedisCache ();
$cache->setRedis($app->getRedis());
$cache->setNamespace(Config::$a['cacheNamespace']);
$app->setCacheDriver($cache);