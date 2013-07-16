<?php
use Destiny\Common\Application;
use Destiny\Common\UserRole;
use Destiny\Common\AppEvent;
use Destiny\Common\Service\UserService;
use Destiny\Common\Service\AuthenticationService;
use Destiny\Common\AppException;
use Destiny\Common\Utils\Http;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionCookie;
use Destiny\Common\SessionInstance;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\Service\ChatIntegrationService;
use Destiny\Common\Router;
use Destiny\Common\Routing\AnnotationDirectoryLoader;
use Destiny\Common\Service\RememberMeService;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationReader;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new stdClass ();
$context->log = 'web';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Puts the session into the cache
$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
	ChatIntegrationService::instance ()->updateSession ( $session, $credentials );
} );
// Removes chat session from cache
$session->addCleanupHandler ( function (SessionInstance $session) {
	ChatIntegrationService::instance ()->deleteSession ( $session );
} );

// Start the session if a valid session cookie is found
Session::start ( Session::START_IFCOOKIE );

// Startup the remember me service
RememberMeService::instance ()->startup ();

// Read all the @Route annotations from the classes within [lib]Destiny/Web/Action
// Would be nice if a RedisFileCacheReader existed, or could be custom built
$reader = new FileCacheReader ( new AnnotationReader (), realpath ( Config::$a ['cache'] ['path'] ) . '/annotation/' );
$app->setAnnotationReader ( $reader );
$app->setRouter ( new Router ( AnnotationDirectoryLoader::load ( $reader, _LIBDIR . '/', 'Destiny/Action/Web' ) ) );

// Attempts to find a route and execute the action
$app->executeRequest ( (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '', $_SERVER ['REQUEST_METHOD'] );
?>