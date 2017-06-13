<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\ControllerAnnotationLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// This should be in the server config
ini_set('date.timezone', 'UTC');

// Used when the full path is needed to the base directory
define('_BASEDIR', realpath(__DIR__ . '/../'));
define('PP_CONFIG_PATH', _BASEDIR . '/config/');
$loader = require _BASEDIR . '/vendor/autoload.php';

Config::load(array_replace_recursive(
    require _BASEDIR . '/config/config.php',
    require _BASEDIR . '/config/config.local.php',
    json_decode(file_get_contents(_BASEDIR . '/package.json'), true)
));
set_include_path(get_include_path() . PATH_SEPARATOR . Config::$a['tpl']['path']);

// Required to auto-load custom annotations
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = Application::instance();
$app->setLoader($loader);

$logger = new Logger ('web');
$logger->pushHandler(new StreamHandler (Config::$a ['log'] ['path'] . 'web.log', Logger::CRITICAL));
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

$router = new Router();
$app->setRouter($router);
$app->setAnnotationReader(new Doctrine\Common\Annotations\CachedReader(new AnnotationReader(), $cache));

ControllerAnnotationLoader::loadClasses(
    new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Controllers/'),
    $app->getAnnotationReader(),
    $router
);

foreach (Config::$a['links'] as $path => $url) {
    $router->addRoute(new Route([
        'path' => $path,
        'url' => $url
    ]));
}