<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Routing\Route;
use Destiny\Common\Request;

class Router {

    /**
     * @var Route[]
     */
    private $routes = array();

    /**
     * Setup the router
     * @param array $routes
     */
    public function __construct(array $routes = null) {
        if (! empty ( $routes )) {
            $this->setRoutes ( $routes );
        }
    }

    /**
     * Get the route collection
     * @return Route[]
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Set the route collection
     * @param Route[] $routes
     */
    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }
    
    /**
     * Add a route
     * @param Route $route
     */
    public function addRoute(Route $route){
        $this->routes[] = $route;
    }

    /**
     * Find a route
     *
     * @param Request $request
     * @return \Destiny\Common\Routing\Route
     */
    public function findRoute(Request $request) {
        $path = $request->path ();
        $method = $request->method ();
        for($i = 0; $i < count ( $this->routes ); ++ $i) {
            if ($this->routes [$i]->testPath ( $path, $method )) {
                return $this->routes [$i];
            }
        }
        return null;
    }

}
?>