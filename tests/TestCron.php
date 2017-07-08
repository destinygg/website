<?php

use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Log;
use Destiny\Common\Scheduler;
use Destiny\Common\TaskAnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;

class TestCron extends PHPUnit\Framework\TestCase {

    public function testCronTasks(){
        $scheduler = new Scheduler ();
        TaskAnnotationLoader::loadClasses(
            new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Tasks/'),
            new AnnotationReader(),
            $scheduler
        );
        $scheduler->loadTasks();
        foreach ($scheduler->schedule as $task) {
            $class = $scheduler->getTaskClass($task);
            Log::info("Executing {class} ...", ['class' => get_class($class)]);
            $class->execute();
        }
        $this->assertTrue(count($scheduler->schedule) > 0);
    }
}