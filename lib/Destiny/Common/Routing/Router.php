<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Request;

class Router {

    /**
     * @var Route[]
     */
    private $routes = [];

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
        $rawPath = $request->path ();
        $preparedPath = $this->prepareUriPath ( $rawPath );
        for($i = 0; $i < count ( $this->routes ); ++$i) {
            if ($this->testRoute ( $this->routes[$i], $preparedPath, $rawPath, $method )) {
                return $this->routes[$i];
            }
        }
        return null;
    }

    /**
     * @param Route $route
     * @param string $preparedUriPath
     * @param string $rawUriPath
     * @param string $httpMethod
     * @return boolean
     */
    protected function testRoute(Route $route, $preparedUriPath, $rawUriPath, $httpMethod) {
        $routeHttpMethod = $route->getHttpMethod();
        if (empty ( $routeHttpMethod ) || in_array ( $httpMethod, $routeHttpMethod )) {
            return (strcasecmp ( $route->getPath (), $preparedUriPath ) === 0 || RoutePathParser::match ( $route->getPath (), $preparedUriPath )) ||
                   (strcasecmp ( $route->getPath (), $rawUriPath ) === 0 || RoutePathParser::match ( $route->getPath (), $rawUriPath ));
        }
        return false;
    }

    /**
     * @param Route $route
     * @param string $uriPath
     * @return array
     */
    public function getRoutePathParams(Route $route, $uriPath) {
        return RoutePathParser::search ( $route->getPath (), $this->prepareUriPath($uriPath) );
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