<?php
namespace Destiny\Common;

use Destiny\Common\Annotation;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;

abstract class TaskAnnotationLoader {

    /**
     * @param DirectoryClassIterator $classIterator
     * @param Reader $reader
     * @param Scheduler $scheduler
     * @throws \ReflectionException
     */
    public static function loadClasses(DirectoryClassIterator $classIterator, Reader $reader, Scheduler $scheduler) {
        $annot = new ReflectionClass(new Annotation\Schedule());
        foreach ($classIterator as $refl) {
            self::loadClass($annot, $refl, $reader, $scheduler);
        }
    }

    private static function loadClass(ReflectionClass $annot, ReflectionClass $class, Reader $reader, Scheduler $scheduler) {
        /** @var \Destiny\Common\Annotation\Schedule $annotation */
        $annotation = $reader->getClassAnnotation($class, $annot->getName());
        if (!empty($annotation)) {
            $scheduler->addTask($class->getShortName(), [
                'class' => $class->getName(),
                'action' => $class->getShortName(),
                'frequency' => $annotation->frequency,
                'period' => $annotation->period
            ]);
        }
    }

}