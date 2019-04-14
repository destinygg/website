<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Options;

class Request {

    /** @var string */
    public $address;

    /** @var string */
    public $method;

    /** @var string */
    public $uri;

    /** @var array */
    public $get;

    /** @var array */
    public $post;

    /** @var array */
    public $headers;

    public function __construct(array $options = null){
        Options::setOptions($this, $options);
    }

    public function getBody(){
        return file_get_contents('php://input');
    }
    
    public function path(){
        return parse_url ( $this->uri, PHP_URL_PATH );
    }
    
    public function address() {
        return $this->address;
    }

    public function method() {
        return $this->method;
    }

    public function uri() {
        return $this->uri;
    }
    
    public function get() {
        return $this->get;
    }

    public function post() {
        return $this->post;
    }

    public function header($name) {
        return $this->headers[$name] ?? null;
    }

    public function param($name) {
        return $this->get[$name] ?? $this->post[$name] ?? null;
    }

}