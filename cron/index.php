<?php

use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Application;
use Destiny\Common\Cron\Scheduler;
use Destiny\Common\Cron\TaskAnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Destiny\Common\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . '/../lib/boot.app.php';
Log::$log->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
$app = Application::instance();

try {
    $scheduler = new Scheduler ();
    TaskAnnotationLoader::loadClasses(
        new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Tasks/'),
        new AnnotationReader(),
        $scheduler
    );
    $scheduler->loadTasks();
    $scheduler->execute();
} catch (Exception $e) {
    Log::error("Could not setup scheduler. " . $e->getMessage());
    exit(1);
}
