<?php
namespace Destiny\Common;

class Request {
    
    private $ipAddress; // request Ip Address
    private $method; // request Method
    private $uri; // request URL
    private $get; // _GET vars
    private $post; // _POST vars

    public function __construct(){
        if(isset ( $_SERVER ['REQUEST_URI'] ))
            $this->uri = $_SERVER ['REQUEST_URI'];
        
        if(isset ( $_SERVER ['REQUEST_METHOD'] ))
            $this->method = $_SERVER ['REQUEST_METHOD'];
        
        if (isset ( $_GET ))
            $this->get = $_GET;
        
        if (isset ( $_POST ))
            $this->post = $_POST;

        if (! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) {
            // check ip from share internet
            $this->ipAddress = $_SERVER ['HTTP_CLIENT_IP'];
        } elseif (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
            // to check ip is pass from proxy
            $this->ipAddress = $_SERVER ['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ipAddress = $_SERVER ['REMOTE_ADDR'];
        }
    }
    
    public function path(){
        return parse_url ( $this->uri, PHP_URL_PATH );
    }
    
    public function ipAddress() {
        return $this->ipAddress;
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

}
?>