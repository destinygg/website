<?php
namespace Destiny\Routing;

use Destiny\AppException;
use Destiny\Application;
use Destiny\Route;
use Destiny\Annotation\HttpMethod;
use Destiny\Annotation\Secure;
use Doctrine\Common\Annotations\Reader;
use \SplFileInfo;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \ReflectionClass;
use \ReflectionMethod;

abstract class AnnotationDirectoryLoader {

	/**
	 * Ported from Doctrine class
	 * Load all files in a folder
	 *
	 * @param Reader $reader
	 * @param string $path
	 * @return array
	 */
	public static function load(Reader $reader, $path) {
		$routes = array ();
		// Get and sort all files in a specific directory
		$files = iterator_to_array ( new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $path ), RecursiveIteratorIterator::SELF_FIRST ) );
		usort ( $files, function (SplFileInfo $a, SplFileInfo $b) {
			return ( string ) $a > ( string ) $b ? 1 : - 1;
		} );
		// Run through all the public classes, that have Action annotations, check for Route annotations
		foreach ( $files as $file ) {
			if ('.php' !== substr ( $file->getFilename (), - 4 )) continue;
			
			$class = self::findClass ( $file );
			if (! $class) continue;
			
			$refl = new ReflectionClass ( $class );
			if ($refl->isAbstract ()) continue;
			
			$annotation = $reader->getClassAnnotations ( $refl, 'Destiny\Annotation\Action' );
			if (empty ( $annotation )) continue;
			
			$methods = $refl->getMethods ( ReflectionMethod::IS_PUBLIC );
			foreach ( $methods as $method ) {
				$methodRoutes = $reader->getMethodAnnotations ( $method );
				foreach ( $methodRoutes as $route ) {
					
					if (! ($route instanceof \Destiny\Annotation\Route))
						continue;
					
					$httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Annotation\HttpMethod' );
					$secure = $reader->getMethodAnnotation ( $method, 'Destiny\Annotation\Secure' );
					$routes [] = new Route ( array (
						'path' => $route->path,
						'classMethod' => $method->name,
						'class' => $refl->name,
						'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
						'secure' => ($secure) ? $secure->roles : null 
					) );
				}
			}
		}
		return $routes;
	}

	/**
	 * Ported from Doctrine class
	 * Returns the full class name for the first class in the file.
	 *
	 * @param string $file A PHP file path
	 * @return string false class name if found, false otherwise
	 */
	protected static function findClass($file) {
		$class = false;
		$namespace = false;
		$tokens = token_get_all ( file_get_contents ( $file ) );
		for($i = 0, $count = count ( $tokens ); $i < $count; $i ++) {
			$token = $tokens [$i];
			if (! is_array ( $token )) continue;
			if (true === $class && T_STRING === $token [0]) return $namespace . '\\' . $token [1];
			if (true === $namespace && T_STRING === $token [0]) {
				$namespace = '';
				do {
					$namespace .= $token [1];
					$token = $tokens [++ $i];
				} while ( $i < $count && is_array ( $token ) && in_array ( $token [0], array (
					T_NS_SEPARATOR,
					T_STRING 
				) ) );
			}
			if (T_CLASS === $token [0]) $class = true;
			if (T_NAMESPACE === $token [0]) $namespace = true;
		}
		return false;
	}

}