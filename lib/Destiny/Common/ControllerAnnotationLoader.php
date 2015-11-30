<?php
namespace Destiny\Common;

use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;

abstract class ControllerAnnotationLoader {
    
    public static function loadClasses(DirectoryClassIterator $classIterator, Reader $reader, Router $router) {
        foreach ( $classIterator as $refl ) {
            self::loadClass ( $refl, $reader, $router );
        }
    }
    
    public static function loadClass(ReflectionClass $refl, Reader $reader, Router $router) {
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

            for($i=0; $i < count($routes); ++$i){
                $router->addRoute ( new Route ( array (
                    'path' => $routes[$i]->path,
                    'classMethod' => $method->name,
                    'class' => $refl->name,
                    'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
                    'secure' => ($secure) ? $secure->roles : null
                ) ) );
            }
        }
    }
}