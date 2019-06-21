<?php
namespace Destiny\Common\Routing;

use Destiny\Common\Utils\Options;

class Route {

    public $path;
    public $class;
    public $classMethod;
    public $httpMethod;
    public $secure;
    public $feature;
    public $url;
    public $responseBody;
    public $privateKeys;
    public $audit;

    public function __construct(array $params = null) {
        if (!empty ($params)) {
            Options::setOptions($this, $params);
        }
    }

    function __sleep() {
        return [
            'path',
            'class',
            'classMethod',
            'httpMethod',
            'secure',
            'feature',
            'url',
            'privateKeys',
            'responseBody',
            'audit'
        ];
    }

    public function getPath() {
        return $this->path;
    }

    public function getClass() {
        return $this->class;
    }

    public function getClassMethod() {
        return $this->classMethod;
    }

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function getSecure() {
        return $this->secure;
    }

    public function isSecure() {
        return !empty($this->secure) || !empty($this->feature) || !empty($this->privateKeys);
    }

    public function getFeature() {
        return $this->feature;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getResponseBody() {
        return $this->responseBody;
    }

    public function getPrivateKeys() {
        return $this->privateKeys;
    }

    public function getAudit() {
        return $this->audit;
    }

}