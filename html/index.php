<?php
$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );

use Destiny\Application;
use Destiny\Config;
use Destiny\Session;

Config::load ( $base . '/lib/config.php' );
$app = Application::getInstance ();
Session::init ();

// Admins only
$app->bind ( '/^\/admin/i', function (Application $app) {
	if (! Session::getAuthorized () || ! Session::hasRole ( 'admin' )) {
		$app->error ( 403 );
	}
} );

// Logged in only
$app->bind ( '/^\/[profile|order|subscribe]/i', function (Application $app) {
	if (! Session::getAuthorized ()) {
		$app->error ( 401 );
	}
} );

// "Pages"
$app->bind ( '/^\/$/i'						, './tpl/home.php' );
$app->bind ( '/^\/league[\/]?$/i'			, './tpl/league.php' );
$app->bind ( '/^\/profile[\/]?$/i'			, './tpl/profile.php' );
$app->bind ( '/^\/schedule[\/]?$/i'			, './tpl/schedule.php' );
$app->bind ( '/^\/subscribe[\/]?$/i'		, './tpl/subscribe.php' );
$app->bind ( '/^\/subscribe\/new[\/]?$/i'	, './tpl/subscribenew.php' );
$app->bind ( '/^\/admin[\/]?$/i'			, './tpl/admin.php' );

// "Easy" way to invoke actions based on the URL
$app->bindNamespace ( 'Destiny\Action' );

// Nothing routed
$app->error ( 404 );
?>