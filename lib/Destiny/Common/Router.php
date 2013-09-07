<?php
namespace Destiny\Common;

class Router {

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
	 * @return array<Route>
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * Set the route collection
	 * @param array<Route> $routes
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
	 * Find a route by path
	 *
	 * @return Route
	 */
	public function findRoute($path, $method) {
		$ext = pathinfo ( $path, PATHINFO_EXTENSION );
		if (! empty ( $ext )) {
			$path = substr ( $path, 0, - (strlen ( $ext ) + 1) );
		}
		if (strlen ( $path ) > 1 && substr ( $path, - 1 ) === '/') {
			$path = substr ( $path, 0, - 1 );
		}
		for($i = 0; $i < count ( $this->routes ); ++ $i) {
			if ($this->routes [$i]->testPath ( $path, $method )) {
				return $this->routes [$i];
			}
		}
		return null;
	}

}
?>