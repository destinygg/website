<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Application;
use Destiny\Common\DirectoryClassIterator;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;

abstract class RouteAnnotationClassLoader {
    
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
            // Get all the route annotations
            $routes = array ();
            $annotations = $reader->getMethodAnnotations ( $method );
            for($i=0; $i < count($annotations); ++$i){
                if($annotations[$i] instanceof \Destiny\Common\Annotation\Route){
                    $routes[] = $annotations[$i];
                }
            }
            // No routes, continue
            if(count($routes) <= 0)
                continue;
            
            // We have 1 or many routes, add to the router
            $httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\HttpMethod' );
            $secure = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Secure' );
            $feature = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Feature' );
            for($i=0; $i < count($routes); ++$i){
                $router->addRoute ( new Route ( array (
                    'path' => $routes[$i]->path,
                    'classMethod' => $method->name,
                    'class' => $refl->name,
                    'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
                    'secure' => ($secure) ? $secure->roles : null,
                    'feature' => ($feature) ? $feature->features : null
                ) ) );
            }
        }
    }
}
?>