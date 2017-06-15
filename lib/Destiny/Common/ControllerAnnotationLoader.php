<?php
namespace Destiny\Common;

use Destiny\Common\Annotation;
use Destiny\Common\Routing\Route;
use Destiny\Common\Routing\Router;
use Doctrine\Common\Annotations\Reader;
use \ReflectionClass;
use \ReflectionMethod;

class ControllerAnnotationLoader {

    /**
     * @var ReflectionClass
     */
    private $controllerRef;

    /**
     * @var ReflectionClass
     */
    private $responseBodyRef;

    /**
     * @var ReflectionClass
     */
    private $httpMethodRef;

    /**
     * @var ReflectionClass
     */
    private $secureRef;

    /**
     * @var ReflectionClass
     */
    private $routeRef;

    /**
     * @param DirectoryClassIterator $classIterator
     * @param Reader $reader
     * @param Router $router
     */
    public function loadClasses(DirectoryClassIterator $classIterator, Reader $reader, Router $router) {
        $this->controllerRef = new ReflectionClass(new Annotation\Controller());
        $this->responseBodyRef = new ReflectionClass(new Annotation\ResponseBody());
        $this->httpMethodRef = new ReflectionClass(new Annotation\HttpMethod());
        $this->secureRef = new ReflectionClass(new Annotation\Secure());
        $this->routeRef = new ReflectionClass(new Annotation\Route());
        foreach ($classIterator as $refl) {
            $annotation = $reader->getClassAnnotation($refl, $this->controllerRef->getName());
            if (!empty($annotation)) {
                $this->loadClass($refl, $reader, $router);
            }
        }
    }

    /**
     * @param ReflectionClass $classRef
     * @param Reader $reader
     * @param Router $router
     */
    public function loadClass(ReflectionClass $classRef, Reader $reader, Router $router) {
        $methods = $classRef->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            /** @var Route[] $routes */
            $routes = [];
            $annotations = $reader->getMethodAnnotations($method);
            for ($i = 0; $i < count($annotations); ++$i) {
                if ($this->routeRef->isInstance($annotations[$i])) {
                    $routes[] = $annotations[$i];
                }
            }
            if (count($routes) > 0) {
                $responseBody = $reader->getMethodAnnotation($method, $this->responseBodyRef->getName());
                $httpMethod = $reader->getMethodAnnotation($method, $this->httpMethodRef->getName());
                $secure = $reader->getMethodAnnotation($method, $this->secureRef->getName());
                for ($i = 0; $i < count($routes); ++$i) {
                    $router->addRoute(new Route([
                        'path' => $routes[$i]->path,
                        'classMethod' => $method->name,
                        'responseBody' => !!$responseBody,
                        'class' => $classRef->name,
                        'httpMethod' => ($httpMethod) ? $httpMethod->allow : null,
                        'secure' => ($secure) ? $secure->roles : null
                    ]));
                }
            }
        }
    }
}