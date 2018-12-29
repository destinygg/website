<?php
use Destiny\Common\ControllerAnnotationLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\AnnotationReader;

class RoutesTest extends PHPUnit\Framework\TestCase {

    /**
     * @throws ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function testRoutes() {
        $router = new Router();
        $annotationLoader = new ControllerAnnotationLoader();
        $annotationLoader->loadClasses(
            new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Controllers/'),
            new AnnotationReader(),
            $router
        );
        $annotationLoader = null;
        $routes = $router->getRoutes();
        usort($routes, function(Route $a, Route $b){
            if($a->getPath() < $b->getPath())
                return -1;
            if($a->getPath() > $b->getPath())
                return 1;
            return 0;
        });
        foreach($routes as $route){
            $method = $route->getHttpMethod();
            $join = ' [' . join(',', ($method ? $method : ['*'])) .'] ';
            echo str_pad($join, 15, " ") . "" . $route->getPath() . PHP_EOL;
        }
        self::assertTrue(count($routes) > 0);
    }

}