<?php
namespace Destiny\Common\Routing;

use Destiny\Common\AppException;
use Destiny\Common\Application;
use Destiny\Common\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
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
	public static function load(Reader $reader, $base, $path) {
		$routes = array ();
		$files = self::getFiles ( $base, $path );
		// Run through all the public classes, that have Action annotations, check for Route annotations
		foreach ( $files as $file ) {
			if ('.php' !== substr ( $file->getFilename (), - 4 )) continue;
			
			// PSR-0 format namespace / folder / filename
			// strip the base off, and treat the rest as the namespace path, - the .php
			$class = str_replace ( '/', '\\', substr ( $file->getPathName (), strlen ( $base ), - 4 ) );
			
			if (! $class) continue;
			
			$refl = new ReflectionClass ( $class );
			if ($refl->isAbstract ()) continue;
			
			$annotation = $reader->getClassAnnotation ( $refl, 'Destiny\Common\Annotation\Action' );
			if (empty ( $annotation )) continue;
			
			$methods = $refl->getMethods ( ReflectionMethod::IS_PUBLIC );
			foreach ( $methods as $method ) {
				$methodRoutes = $reader->getMethodAnnotations ( $method );
				foreach ( $methodRoutes as $route ) {
					
					if (! ($route instanceof \Destiny\Common\Annotation\Route)) continue;
					
					$httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\HttpMethod' );
					$secure = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Secure' );
					$feature = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Feature' );
					$routes [] = new Route ( array (
						'path' => $route->path,
						'classMethod' => $method->name,
						'class' => $refl->name,
						'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
						'secure' => ($secure) ? $secure->roles : null,
						'feature' => ($feature) ? $feature->features : null 
					) );
				}
			}
		}
		return $routes;
	}

	/**
	 * Get all the files in a folder
	 * @param string $base
	 * @param string $path
	 * @return array<SplFileInfo>
	 */
	private static function getFiles($base, $path) {
		// Get and sort all files in a specific directory
		$files = iterator_to_array ( new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( $base . $path ), RecursiveIteratorIterator::SELF_FIRST ) );
		usort ( $files, function (SplFileInfo $a, SplFileInfo $b) {
			return ( string ) $a > ( string ) $b ? 1 : - 1;
		} );
		return $files;
	}

}