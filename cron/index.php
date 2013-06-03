<?php
$base = realpath ( __DIR__ . '/../' );
$loader = require $base . '/vendor/autoload.php';
$loader->add ( 'Destiny', $base . '/lib/' );

use Destiny\Application;
use Destiny\Config;
use Destiny\Cron;

Config::load ( $base . '/lib/config.php' );

// Note: Cron is run every minute. (there can be a time where actions are executed before they have ended)
set_time_limit ( 180 );
$cron = new Cron ();
$cron->setLogPath ( Config::$a ['log'] ['path'] );

$cron->add ( 'Recentgames'	, Config::$a ['fantasy'] ['intervals'] ['track'] );
$cron->add ( 'Ingame'		, Config::$a ['fantasy'] ['intervals'] ['ingame'] );
$cron->add ( 'Aggregate'	, Config::$a ['fantasy'] ['intervals'] ['aggregate'] );
$cron->add ( 'Freechamps'	, Config::$a ['fantasy'] ['intervals'] ['freechamp'] );
$cron->add ( 'Subscriptions', Config::$a ['intervals'] ['Subscriptions'] );
$cron->add ( 'Sessiongc'	, Config::$a ['intervals'] ['Sessiongc'] );

// Need to update the status, since its never called via the front end.
Destiny\Service\Leagueapi::getInstance ()->getStatus ();

array_shift ( $argv );
$cron->execute ( $argv );
?>