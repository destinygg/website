<?php
namespace Destiny;

class Router {

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
	public function setRoutes($routes) {
		$this->routes = $routes;
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
		if (strlen($path) > 1 && substr ( $path, - 1 ) === '/') {
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