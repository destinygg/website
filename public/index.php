<?php
use Destiny\AppEvent;
use Destiny\Service\UserService;
use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\SessionCredentials;
use Destiny\SessionCookie;
use Destiny\SessionInstance;
use Destiny\Session;
use Destiny\Config;

$context->log = 'http';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Setup user session
$app->setSession ( new SessionInstance () );
$session = $app->getSession ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );

// Puts all the credentials on the session data
$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
	$params = $credentials->getData ();
	foreach ( $params as $name => $value ) {
		$session->set ( $name, $value );
	}
} );

// Puts the session into the cache
$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
	$redis = Application::instance ()->getRedis ();
	if (! empty ( $redis )) {
		$redis->set ( sprintf ( 'CHAT:%s', $session->getSessionId () ), json_encode ( $credentials->getData () ), 30 * 24 * 60 * 60 );
	}
} );
// Removes session from cache
$session->addCleanupHandler ( function (SessionInstance $session) {
	$redis = Application::instance ()->getRedis ();
	if (! empty ( $redis )) {
		$redis->delete ( sprintf ( 'CHAT:%s', $session->getSessionId () ) );
	}
} );

// Start the session if a valid cookie is found
Session::start ( Session::START_IFVALIDCOOKIE );

// Remember me
if (! Session::isStarted ()) {
	$authManager = AuthenticationService::instance ();
	$userId = $authManager->getRememberMe ();
	if ($userId !== false) {
		$userManager = UserService::instance ();
		$user = $userManager->getUserById ( $userId );
		if (! empty ( $user )) {
			Session::start ( Session::START_NOCOOKIE );
			$authManager->login ( $user, 'rememberme' );
			$authManager->setRememberMe ( $user );
			$app->addEvent ( new AppEvent ( array (
				'type' => AppEvent::EVENT_DANGER,
				'label' => 'We remember!',
				'message' => sprintf ( 'Please logout if you are not "%s"', Session::getCredentials ()->getUsername () ) 
			) ) );
		}
	}
}

// Dev/Admins only
$app->bind ( '/^\/(subscribe|profile\/subscription|payment)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [admin] %s', $app->getPath () ) );
	if (! Session::hasRole ( \Destiny\UserRole::ADMIN )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// Admins only
$app->bind ( '/^\/(admin|order|subscribe)/i', function (Application $app) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [admin] %s', $app->getPath () ) );
	if (! Session::hasRole ( \Destiny\UserRole::USER ) || ! Session::hasRole ( \Destiny\UserRole::ADMIN )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// Logged in only
$app->bind ( '/^\/(profile|order|subscribe|fantasy|payment|league\/[*]+)/i', function (Application $app, array $params) {
	$app->getLogger ()->debug ( sprintf ( 'Security: [user] %s', $app->getPath () ) );
	if (! Session::hasRole ( \Destiny\UserRole::USER )) {
		$app->error ( Http::STATUS_UNAUTHORIZED );
	}
} );

// /league/game/9999
$app->bind ( '/^\/league\/game\/(?<gameId>[0-9]+)/i', function (Application $app, array $params) {
	$app->executeAction ( new Destiny\Action\League\Game (), $params );
} );
// /payment/details/9999
$app->bind ( '/^\/payment\/details\/(?<id>[0-9]+)/i', function (Application $app, array $params) {
	$app->executeAction ( new Destiny\Action\Payment\Details (), $params );
} );

// "Easy" way to invoke actions based on the URL, second param is the default action
$app->bindNamespace ( 'Destiny\Action', 'Home' );

// Nothing routed
$app->error ( Http::STATUS_NOT_FOUND );
?>