<?php
require __DIR__ . '/../lib/boot.app.php';
$app = Destiny\Common\Application::instance ();
$scheduler = new Destiny\Common\Scheduler ();
$scheduler->loadSchedule();
$scheduler->execute();