<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Request;
use Destiny\Common\Utils\String\Params;

class Router {

    /**
     * @var Route[]
     */
    private $routes = array();

    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes = null) {
        if (! empty ( $routes )) {
            $this->setRoutes ( $routes );
        }
    }

    /**
     * @return Route[]
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * @param Route[] $routes
     */
    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }
    
    /**
     * @param Route $route
     */
    public function addRoute(Route $route){
        $this->routes[] = $route;
    }

    /**
     * @param Request $request
     * @return Route|null
     */
    public function findRoute(Request $request) {
        $method = $request->method ();
        $path = $this->prepareUriPath ( $request->path () );
        for($i = 0; $i < count ( $this->routes ); ++$i) {
            if ($this->testRoute ( $this->routes[$i], $path, $method )) {
                return $this->routes[$i];
            }
        }
        return null;
    }

    /**
     * @param Route $route
     * @param string $uriPath
     * @param string $httpMethod
     * @return boolean
     */
    protected function testRoute(Route $route, $uriPath, $httpMethod) {
        $routeHttpMethod = $route->getHttpMethod();
        if (empty ( $routeHttpMethod ) || in_array ( $httpMethod, $routeHttpMethod )) {
            return (strcasecmp ( $route->getPath (), $uriPath ) === 0 || Params::match ( $route->getPath (), $uriPath ));
        }
        return false;
    }

    /**
     * @param Route $route
     * @param string $uriPath
     * @return array
     */
    public function getRoutePathParams(Route $route, $uriPath) {
        return Params::search ( $route->getPath (), $this->prepareUriPath($uriPath) );
    }

    /**
     * Removes extension and trailing slash from the $path
     * @param string $path
     * @return string
     */
    protected function prepareUriPath($path) {
        $extension = pathinfo ( $path, PATHINFO_EXTENSION );
        if (! empty ( $extension ))
            $path = substr ( $path, 0, - (strlen ( $extension ) + 1) );
        if (strlen ( $path ) > 1 && substr ( $path, - 1 ) === '/')
            $path = substr ( $path, 0, - 1 );
        return $path;
    }

}