<?php
use Destiny\Common\Application;
use Destiny\Common\ControllerAnnotationLoader;
use Destiny\Common\DirectoryClassIterator;
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

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new Cookie ( 'sid', Config::$a ['cookie'] ) );
$session->setRememberMeCookie ( new Cookie ( 'rememberme', Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Startup the authentication service, handles logged in session, remember me session
AuthenticationService::instance ()->startSession();

// Routing
$router = new Router();
(new ControllerAnnotationLoader())->loadClasses(
    new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Controllers/'),
    new Doctrine\Common\Annotations\CachedReader(new AnnotationReader(), $app->getCacheDriver()),
    $router
);
foreach (Config::$a['links'] as $path => $url) {
    $router->addRoute(new Route([
        'path' => $path,
        'url' => $url
    ]));
}
$app->setRouter($router);
//

// Attempts to find a route and execute it
$app->executeRequest(new Request([
    'uri'           => $_SERVER ['REQUEST_URI'],
    'method'        => $_SERVER ['REQUEST_METHOD'],
    'headers'       => Http::extractHeaders($_SERVER),
    'ipAddress'     => Http::extractIpAddress($_SERVER),
    'get'           => $_GET,
    'post'          => $_POST
]));