<?php

use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Log;
use Destiny\Common\Cron\Scheduler;
use Destiny\Common\Cron\TaskAnnotationLoader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

class TestCron extends PHPUnit\Framework\TestCase {

    /**
     * @throws \Destiny\Common\Exception
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function testCronTasks(){
        $scheduler = new Scheduler ();
        TaskAnnotationLoader::loadClasses(
            new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Tasks/'),
            new AnnotationReader(),
            $scheduler
        );
        // $scheduler->loadTasks();
        foreach ($scheduler->schedule as $task) {
            $class = $scheduler->getTaskClass($task);
            Log::info("Cron task {class} ...", ['class' => get_class($class)]);
            // $class->execute();
        }
        self::assertTrue(count($scheduler->schedule) > 0);
    }
}