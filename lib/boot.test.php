<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Log;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

define('_APP_VERSION', '2.3.28'); // auto-generated: 1539266270439
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

$app = Application::instance();
$app->setLoader($loader);
$app->setCache(new Doctrine\Common\Cache\ArrayCache());

Log::$log = new Logger('web');
Log::$log->pushProcessor(new PsrLogMessageProcessor());
Log::$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

try {
    $app->setDbal(DriverManager::getConnection(Config::$a ['db']));
} catch (\Exception $e) {
    Log::error("Could not setup DB connection. " . $e->getMessage());
    exit(1);
}
