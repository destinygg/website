<?php

use Destiny\Common\Application;
use Destiny\Common\ControllerAnnotationLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Log;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionInstance;
use Destiny\Common\Cookie;
use Destiny\Common\Config;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Request;
use Destiny\Common\Utils\Http;
use Doctrine\Common\Annotations\AnnotationReader;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

require __DIR__ . '/../lib/boot.app.php';
$app = Application::instance ();

try {
    // Routing
    $router = new Router();
    ControllerAnnotationLoader::factory(
        new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Controllers/'),
        new Doctrine\Common\Annotations\CachedReader(new AnnotationReader(), $app->getCache()),
        $router
    );
    foreach (Config::$a['links'] as $path => $url) {
        $router->addRoute(new Route(['path' => $path, 'url' => $url]));
    }
    $app->setRouter($router);
    //

    // Setup user session
    $session = new SessionInstance();
    $session->setSessionCookie(new Cookie('sid', Config::$a['cookie']));
    $session->setRememberMeCookie(new Cookie('rememberme', Config::$a['cookie']));
    $session->setCredentials(new SessionCredentials());
    $app->setSession($session);
    AuthenticationService::instance()->startSession();
    //
} catch (Exception $e) {
    Log::error($e->getMessage());
    echo "Application failed to start. Check the error logs for more info.";
}

// Attempts to find a route and execute it
$app->executeRequest(new Request([
    'uri'       => $_SERVER['REQUEST_URI'],
    'method'    => $_SERVER['REQUEST_METHOD'],
    'headers'   => Http::extractHeaders($_SERVER),
    'ipAddress' => Http::extractIpAddress($_SERVER),
    'get'       => $_GET,
    'post'      => $_POST
]));