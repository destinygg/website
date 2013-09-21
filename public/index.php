<?php
use Destiny\Common\Application;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionCookie;
use Destiny\Common\SessionInstance;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Routing\Router;
use Destiny\Common\Routing\RouteAnnotationClassLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Authentication\Service\RememberMeService;
use Destiny\Authentication\Service\AuthenticationService;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationReader;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new stdClass ();
$context->log = 'web';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();
$app->setRouter ( new Router () );
$app->setAnnotationReader ( new FileCacheReader ( new AnnotationReader (), realpath ( Config::$a ['cache'] ['path'] ) . '/annotation/' ) );

// Annotation reader and routing
RouteAnnotationClassLoader::loadClasses ( new DirectoryClassIterator ( _LIBDIR . '/', 'Destiny/Action/' ), $app->getAnnotationReader () );

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Start the session if a valid session cookie is found
Session::start ( Session::START_IFCOOKIE );

// Startup the remember me and auth service
AuthenticationService::instance ()->init ();
RememberMeService::instance ()->init ();

// Attempts to find a route and execute it
$app->executeRequest ( (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '', $_SERVER ['REQUEST_METHOD'] );
?>