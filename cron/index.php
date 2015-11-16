<?php
use Destiny\Common\Application;
use Destiny\Common\Scheduler;

require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();
$scheduler = new Scheduler ();
$scheduler->setLogger ( $app->getLogger () );
$scheduler->loadSchedule();
$scheduler->execute();