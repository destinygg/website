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
use Destiny\Common\Route;
use Destiny\Common\Router;
use Destiny\Common\Routing\AnnotationDirectoryLoader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationReader;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new stdClass ();
$context->log = 'http';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Setup user session
$app->setSession ( new SessionInstance () );
$session = $app->getSession ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );

// Puts the session into the cache
$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
	ChatIntegrationService::instance ()->updateSession ( $session, $credentials );
} );
// Removes session from cache
$session->addCleanupHandler ( function (SessionInstance $session) {
	ChatIntegrationService::instance ()->deleteSession ( $session );
} );

// Start the session if a valid session cookie is found
Session::start ( Session::START_IFCOOKIE );

// Check if the users session has been flagged for update
if (Session::isStarted ()) {
	$userId = Session::getCredentials ()->getUserId ();
	if (! empty ( $userId )) {
		$cache = $app->getCacheDriver ();
		$cacheId = sprintf ( 'refreshusersession-%s', $userId );
		if ($cache->fetch ( $cacheId ) === 1) {
			$cache->delete ( $cacheId );
			$userManager = UserService::instance ();
			$user = $userManager->getUserById ( $userId );
			if (! empty ( $user )) {
				$authService = AuthenticationService::instance ();
				$authService->login ( $user, 'refreshed' );
				$app->addEvent ( new AppEvent ( array (
					'type' => AppEvent::EVENT_INFO,
					'label' => 'Your session has been updated',
					'message' => sprintf ( 'Nothing to worry about %s, just letting you know...', Session::getCredentials ()->getUsername () ) 
				) ) );
			}
		}
	}
}

// If the session hasnt started, or the data is not valid (result from php clearing the session data), check the Remember me cookie
if (! Session::isStarted () || ! Session::getCredentials ()->isValid ()) {
	$authService = AuthenticationService::instance ();
	$userId = $authService->getRememberMe ();
	if ($userId !== false) {
		$userManager = UserService::instance ();
		$user = $userManager->getUserById ( $userId );
		if (! empty ( $user )) {
			$authService->login ( $user, 'rememberme' );
			$authService->setRememberMe ( $user );
			$app->addEvent ( new AppEvent ( array (
				'type' => AppEvent::EVENT_INFO,
				'label' => 'You have been automatically logged in',
				'message' => sprintf ( 'Nothing to worry about %s, just letting you know...', Session::getCredentials ()->getUsername () ) 
			) ) );
		}
	}
}

// Read all the @Route annotations from the classes within [lib]Destiny/Web/Action
// Would be nice if a RedisFileCacheReader existed, or could be custom built
$reader = new FileCacheReader ( new AnnotationReader (), realpath ( Config::$a ['cache'] ['path'] ) . '/annotation/' );
$app->setAnnotationReader ( $reader );
$app->setRouter ( new Router ( AnnotationDirectoryLoader::load ( $reader, _LIBDIR . '/', 'Destiny/Action/Web' ) ) );

// Attempts to find a route and execute the action
$app->executeRequest ( (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '', $_SERVER ['REQUEST_METHOD'] );
?>