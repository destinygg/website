<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Application;
use Destiny\Common\DirectoryClassIterator;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;

abstract class ControllerAnnotationLoader {
    
    public static function loadClasses(DirectoryClassIterator $classIterator, Reader $reader) {
        foreach ( $classIterator as $refl ) {
            self::loadClass ( $refl, $reader );
        }
    }
    
    public static function loadClass(ReflectionClass $refl, Reader $reader) {
        $router = Application::instance ()->getRouter ();
        $annotation = $reader->getClassAnnotation ( $refl, 'Destiny\Common\Annotation\Controller' );
        if (empty ( $annotation))
            return;
        
        $methods = $refl->getMethods ( ReflectionMethod::IS_PUBLIC );
        foreach ( $methods as $method ) {
            /** @var Route[] $routes */
            $routes = array ();

            $annotations = $reader->getMethodAnnotations ( $method );
            for($i=0; $i < count($annotations); ++$i){
                /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                if($annotations[$i] instanceof \Destiny\Common\Annotation\Route){
                    $routes[] = $annotations[$i];
                }
            }

            if(count($routes) <= 0)
                continue;

            /** @var \Destiny\Common\Annotation\HttpMethod $feature */
            $httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\HttpMethod' );
            /** @var \Destiny\Common\Annotation\Secure $feature */
            $secure = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Secure' );
            /** @var \Destiny\Common\Annotation\Feature $feature */
            $feature = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Feature' );
            /** @var \Destiny\Common\Annotation\Transactional $transactional */
            $transactional = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Transactional' );

            for($i=0; $i < count($routes); ++$i){
                $router->addRoute ( new Route ( array (
                    'path' => $routes[$i]->path,
                    'classMethod' => $method->name,
                    'class' => $refl->name,
                    'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
                    'secure' => ($secure) ? $secure->roles : null,
                    'feature' => ($feature) ? $feature->features : null,
                    'transactional' => ($transactional) ? true : false,
                ) ) );
            }
        }
    }
}