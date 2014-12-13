<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Utils\Options;
use Destiny\Common\Utils\String\Params;

class Route {
    
    public $path;
    public $class;
    public $classMethod;
    public $httpMethod;
    public $secure;
    public $feature;

    public function __construct(array $params = null) {
        if (! empty ( $params )) {
            Options::setOptions ( $this, $params );
        }
    }

    /**
     * Serialization
     *
     * @return array
     */
    function __sleep() {
        return array (
            'path',
            'class',
            'classMethod',
            'httpMethod',
            'secure',
            'feature' 
        );
    }

    /**
     * Return the parameters in the path
     * @param string $path
     * @return array
     */
    public function getPathParams($path) {
        $params = Params::search ( $this->getPath (), $path );
        return ($params) ? $params : array ();
    }

    /**
     * Test if the path supplied meets the Route requirements
     *
     * @param string $path The path from the URI
     * @param string $method The HTTP method
     * @return boolean
     */
    public function testPath($path, $method) {
        if (empty ( $this->httpMethod ) || in_array ( $method, $this->httpMethod )) {

            // Remove trailing slash
            if (strlen ( $path ) > 1 && substr ( $path, - 1 ) === '/')
                $path = substr ( $path, 0, - 1 );
                
            // Path without extention
            $extlessPath = $this->stripPathExtention($path);

            // Exact
            if (strcasecmp ( $this->getPath (), $path ) === 0 || strcasecmp ( $this->getPath (), $extlessPath ) === 0)
                return true;

            // Regex match
            if (Params::match ( $this->getPath (), $path ) || Params::match ( $this->getPath (), $extlessPath ))
                return true;
            
        }
        return false;
    }
    
    /**
     * @param string $path
     * @return string
     */
    private function stripPathExtention($path){
        $ext = pathinfo ( $path, PATHINFO_EXTENSION );
        if (! empty ( $ext )) {
            $path = substr ( $path, 0, - (strlen ( $ext ) + 1) );
        }
        return $path;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getClass() {
        return $this->class;
    }

    public function setClass($class) {
        $this->class = $class;
    }

    public function getClassMethod() {
        return $this->classMethod;
    }

    public function setClassMethod($classMethod) {
        $this->classMethod = $classMethod;
    }

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function setHttpMethod($httpMethod) {
        $this->httpMethod = $httpMethod;
    }

    public function getSecure() {
        return $this->secure;
    }

    public function setSecure($secure) {
        $this->secure = $secure;
    }

    public function getFeature() {
        return $this->feature;
    }

    public function setFeature($feature) {
        $this->feature = $feature;
    }

}