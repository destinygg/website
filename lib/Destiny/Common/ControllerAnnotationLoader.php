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

            /** @var \Destiny\Common\Annotation\ResponseBody $responseBody */
            $responseBody = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\ResponseBody' );
            /** @var \Destiny\Common\Annotation\HttpMethod $httpMethod */
            $httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\HttpMethod' );
            /** @var \Destiny\Common\Annotation\Secure $secure */
            $secure = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Secure' );

            for($i=0; $i < count($routes); ++$i){
                $router->addRoute (new Route([
                    'path' => $routes[$i]->path,
                    'classMethod' => $method->name,
                    'responseBody' => !!$responseBody,
                    'class' => $refl->name,
                    'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
                    'secure' => ($secure) ? $secure->roles : null
                ]));
            }
        }
    }
}