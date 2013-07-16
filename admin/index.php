<?php 
use Destiny\Common\Application;
use Destiny\Common\AppException;
use Destiny\Common\Session;
use Destiny\Common\Scheduler;
use Destiny\Common\Config;

$context->log = 'admin';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();
?>