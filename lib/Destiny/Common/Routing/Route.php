<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Utils\Options;

class Route {

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $classMethod;

    /**
     * @var array
     */
    public $httpMethod;

    /**
     * @var array
     */
    public $secure;

    /**
     * @var array
     */
    public $feature;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $responseBody;

    public function __construct(array $params = null) {
        if (! empty ( $params )) {
            Options::setOptions ( $this, $params );
        }
    }

    function __sleep() {
        return array (
            'path',
            'class',
            'classMethod',
            'httpMethod',
            'secure',
            'feature',
            'url',
            'responseBody'
        );
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

    public function isSecure() {
        return !empty($this->secure) || !empty($this->feature);
    }

    public function getFeature() {
        return $this->feature;
    }

    public function setFeature($feature) {
        $this->feature = $feature;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getResponseBody() {
        return $this->responseBody;
    }

    public function setResponseBody($responseBody) {
        $this->responseBody = $responseBody;
    }

}