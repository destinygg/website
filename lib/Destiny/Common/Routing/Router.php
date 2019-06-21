<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Request;

class Router {

    /**
     * @var Route[]
     */
    private $routes = [];

    public function __construct(array $routes = null) {
        if (!empty ($routes)) {
            $this->setRoutes($routes);
        }
    }

    public function getRoutes(): array {
        return $this->routes;
    }

    public function setRoutes(array $routes) {
        $this->routes = $routes;
    }
    
    public function addRoute(Route $route){
        $this->routes[] = $route;
    }

    /**
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

    protected function testRoute(Route $route, string $preparedUriPath, string $rawUriPath, string $httpMethod): bool {
        $routeHttpMethod = $route->getHttpMethod();
        if (empty ( $routeHttpMethod ) || in_array ( $httpMethod, $routeHttpMethod )) {
            return (strcasecmp ( $route->getPath (), $preparedUriPath ) === 0 || RoutePathParser::match ( $route->getPath (), $preparedUriPath )) ||
                   (strcasecmp ( $route->getPath (), $rawUriPath ) === 0 || RoutePathParser::match ( $route->getPath (), $rawUriPath ));
        }
        return false;
    }

    public function getRoutePathParams(Route $route, string $uriPath): array {
        return RoutePathParser::search ( $route->getPath (), $this->prepareUriPath($uriPath) );
    }

    /**
     * Removes extension and trailing slash from the $path
     */
    protected function prepareUriPath(string $path): string {
        $extension = pathinfo ( $path, PATHINFO_EXTENSION );
        if (! empty ( $extension ))
            $path = substr ( $path, 0, - (strlen ( $extension ) + 1) );
        if (strlen ( $path ) > 1 && substr ( $path, - 1 ) === '/')
            $path = substr ( $path, 0, - 1 );
        return $path;
    }

}