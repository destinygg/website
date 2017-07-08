<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

ini_set('date.timezone', 'UTC');
define('_APP_VERSION', '2.0.78'); // auto-generated: 1499361006489
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

Log::$log = new Logger('web');
Log::$log->pushProcessor(new PsrLogMessageProcessor());
Log::$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$app->setDbal(DriverManager::getConnection(Config::$a ['db']));
$app->setCache(new Doctrine\Common\Cache\ArrayCache());