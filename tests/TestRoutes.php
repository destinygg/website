<?php
use Destiny\Common\ControllerAnnotationLoader;
use Destiny\Common\DirectoryClassIterator;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;

class RoutesTest extends PHPUnit\Framework\TestCase {

    /**
     * @return Route[]
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function getRoutes() {
        $router = new Router();
        $annotationLoader = new ControllerAnnotationLoader();
        $annotationLoader->loadClasses(
            new DirectoryClassIterator (_BASEDIR . '/lib/', 'Destiny/Controllers/'),
            new AnnotationReader(),
            $router
        );
        $annotationLoader = null;
        return $router->getRoutes();
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function testRoutes() {
        $routes = $this->getRoutes();
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