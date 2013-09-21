<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Application;
use Destiny\Common\Routing\Route;
use Destiny\Common\DirectoryClassIterator;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;

abstract class RouteAnnotationClassLoader {
	
	public static function loadClasses(DirectoryClassIterator $classIterator, Reader $reader) {
		foreach ( $classIterator as $refl ) {
			self::loadClass ( $refl, $reader );
		}
	}
	
	public static function loadClass(ReflectionClass $refl, Reader $reader) {
		$router = Application::instance ()->getRouter ();
		$annotation = $reader->getClassAnnotation ( $refl, 'Destiny\Common\Annotation\Action' );
		if (empty ( $annotation ))
			return;
		
		$methods = $refl->getMethods ( ReflectionMethod::IS_PUBLIC );
		foreach ( $methods as $method ) {
			$routeAnnotation = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Route' );
			if (! ($routeAnnotation instanceof \Destiny\Common\Annotation\Route))
				continue;
			
			$httpMethod = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\HttpMethod' );
			$secure = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Secure' );
			$feature = $reader->getMethodAnnotation ( $method, 'Destiny\Common\Annotation\Feature' );
			$router->addRoute ( new Route ( array (
					'path' => $routeAnnotation->path,
					'classMethod' => $method->name,
					'class' => $refl->name,
					'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
					'secure' => ($secure) ? $secure->roles : null,
					'feature' => ($feature) ? $feature->features : null 
			) ) );
		}
	}
}
?>