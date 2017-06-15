<?php
use Destiny\Common\DirectoryClassIterator;
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
        /*$scheduler->loadTasks();
        foreach ($scheduler->schedule as $task) {
            $class = $scheduler->getTaskClass($task);
            echo get_class($class) . PHP_EOL;
            echo json_encode($task, JSON_PRETTY_PRINT);
            echo PHP_EOL;
        }*/
        $this->assertTrue(count($scheduler->schedule) > 0);
    }
}