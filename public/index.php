<?php
use Destiny\Common\Application;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionInstance;
use Destiny\Common\Cookie;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Routing\Router;
use Destiny\Common\Routing\RouteAnnotationClassLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Authentication\RememberMeService;
use Destiny\Common\Authentication\AuthenticationService;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Destiny\Common\Request;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new \stdClass ();
$context->log = 'web';
require __DIR__ . '/../lib/boot.php';

$app = Application::instance ();
$app->setRouter ( new Router () );
$app->setAnnotationReader ( new FileCacheReader ( new AnnotationReader (), realpath ( Config::$a ['cache'] ['path'] ) . '/annotation/' ) );

// Annotation reader and routing
RouteAnnotationClassLoader::loadClasses ( new DirectoryClassIterator ( _BASEDIR . '/lib/', 'Destiny/Controllers/' ), $app->getAnnotationReader () );

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new Cookie ( Config::$a ['cookie'] ) );
$session->setRememberMeCookie ( new Cookie ( Config::$a ['rememberme'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Startup the authentication service, handles logged in session, remember me session
AuthenticationService::instance ()->startSession();

// Attempts to find a route and execute it
$app->executeRequest ( new Request() );
?>