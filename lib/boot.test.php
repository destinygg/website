<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

ini_set('date.timezone', 'UTC');
define('_APP_VERSION', '2.0.75'); // auto-generated: 1497453147749
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

$logger = new Logger('web');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$app->setLogger($logger);

$app->setConnection(DriverManager::getConnection(Config::$a ['db']));
$app->setCacheDriver(new Doctrine\Common\Cache\ArrayCache());