<?php
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\TaskAnnotationLoader;
use Destiny\Common\Application;
use Destiny\Common\Scheduler;
use Doctrine\Common\Annotations\AnnotationReader;

require __DIR__ . '/../lib/boot.app.php';
$app = Application::instance();
$scheduler = new Scheduler ();
TaskAnnotationLoader::loadClasses(
    new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Tasks/'),
    new AnnotationReader(),
    $scheduler
);
$scheduler->loadTasks();
$scheduler->execute();