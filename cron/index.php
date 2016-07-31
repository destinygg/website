<?php
require __DIR__ . '/../lib/boot.php';
$app = Destiny\Common\Application::instance ();
$scheduler = new Destiny\Common\Scheduler ();
$scheduler->setLogger ( $app->getLogger () );
$scheduler->loadSchedule();
$scheduler->execute();