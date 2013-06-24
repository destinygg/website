<?php
use Destiny\Service\UserService;
use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\SessionAuthenticationCredentials;
use Destiny\SessionCookieInterface;
use Destiny\SessionInstance;
use Destiny\Session;
use Destiny\Config;

$context->log = 'http';
require __DIR__ . '/../lib/boot.php';

$app = Application::instance ();

$session = Session::setInstance ( new SessionInstance () );
$session->setSessionCookieInterface ( new SessionCookieInterface ( Config::$a ['cookie'] ) );
$session->start ();
$session->setAuthenticationCredentials ( new SessionAuthenticationCredentials ( $session->getData () ) );

// Remember me, when session expires
if (! Session::hasRole ( \Destiny\UserRole::USER )) {
	$authManager = AuthenticationService::instance ();
	$userId = $authManager->getRememberMe ();
	if ($userId !== false) {
		$userManager = UserService::instance ();
		$user = $userManager->getUserById ( $userId );
		$authManager->login ( $user, 'rememberme' );
		$authManager->setRememberMe ( $user );
	}
}

// Admins only
$app->bind ( '/^\/(admin|order|subscribe|profile\/subscribe|profile\/subscription|payment)/i', function (Application $app) {
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

// Friendly url to league game
$app->bind ( '/^\/league\/game\/(?<gameId>[0-9]+)/i', function (Application $app, array $params) {
	$app->executeAction ( new Destiny\Action\League\Game (), $params );
} );

// "Easy" way to invoke actions based on the URL, second param is the default action
$app->bindNamespace ( 'Destiny\Action', 'Home' );

// Nothing routed
$app->error ( Http::STATUS_NOT_FOUND );
?>