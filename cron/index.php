<?php
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\TaskAnnotationLoader;
use Destiny\Common\Application;
use Destiny\Common\Scheduler;
use Doctrine\Common\Annotations\AnnotationReader;
use Destiny\Common\Log;

require __DIR__ . '/../lib/boot.app.php';
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
} catch (\Exception $e) {
    Log::error("Could not setup scheduler. " . $e->getMessage());
    exit(1);
}
