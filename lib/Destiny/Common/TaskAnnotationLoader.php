<?php
namespace Destiny\Common;

use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;

abstract class TaskAnnotationLoader {

    public static function loadClasses(DirectoryClassIterator $classIterator, Reader $reader, Scheduler $scheduler) {
        foreach ( $classIterator as $refl ) {
            self::loadClass ( $refl, $reader, $scheduler );
        }
    }

    public static function loadClass(ReflectionClass $refl, Reader $reader, Scheduler $scheduler) {
        /** @var \Destiny\Common\Annotation\Schedule $annotation */
        $annotation = $reader->getClassAnnotation ( $refl, 'Destiny\Common\Annotation\Schedule' );
        if(!empty($annotation)){
            $scheduler->addTask(array(
                'action' => $refl->getShortName(),
                'frequency' => $annotation->frequency,
                'period' => $annotation->period
            ));
        }
    }

}