<?php
use Destiny\Common\Application;
use Destiny\Common\SessionCredentials;
use Destiny\Common\SessionInstance;
use Destiny\Common\Cookie;
use Destiny\Common\Config;
use Destiny\Common\Authentication\AuthenticationService;
use Destiny\Common\Request;

ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Setup user session
$session = new SessionInstance ();
$session->setSessionCookie ( new Cookie ( 'sid', Config::$a ['cookie'] ) );
$session->setRememberMeCookie ( new Cookie ( 'rememberme', Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );
$app->setSession ( $session );

// Startup the authentication service, handles logged in session, remember me session
AuthenticationService::instance ()->startSession();

// Attempts to find a route and execute it
$app->executeRequest ( new Request() );