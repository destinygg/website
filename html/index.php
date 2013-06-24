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

// Puts the session into redis
if (class_exists ( 'Redis' )) {
	$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
		try {
			if ($session->isStarted ()) {
				$redis = new Redis ();
				$redis->connect ( Config::$a ['redis'] ['host'], Config::$a ['redis'] ['port'] );
				$redisCache = new \Doctrine\Common\Cache\RedisCache ();
				$redisCache->setRedis ( $redis );
				$redisCache->save ( sprintf ( 'CHAT:%s', $session->getSessionId () ), json_encode ( $credentials->getData () ) );
			}
		} catch ( \Exception $e ) {
			$logger = Application::instance ()->getLogger ();
			$logger->error ( 'Could not store the session data in redis' );
		}
	} );
}

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