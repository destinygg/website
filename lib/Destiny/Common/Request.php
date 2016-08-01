<?php
namespace Destiny\Common;

class Request {

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $get;

    /**
     * @var array
     */
    private $post;

    /**
     * @var array
     */
    private $headers;

    public function __construct(){
        if (isset ($_GET))
            $this->get = $_GET;

        if (isset ($_POST))
            $this->post = $_POST;

        if (!empty($_SERVER)) {
            if (isset ($_SERVER ['REQUEST_URI']))
                $this->uri = $_SERVER ['REQUEST_URI'];

            if (isset ($_SERVER ['REQUEST_METHOD']))
                $this->method = $_SERVER ['REQUEST_METHOD'];

            if (!empty ($_SERVER ['HTTP_X_REAL_IP'])) // ip from haproxy
                $this->ipAddress = $_SERVER ['HTTP_X_REAL_IP'];
            else if (!empty ($_SERVER ['HTTP_CLIENT_IP'])) // check ip from share internet
                $this->ipAddress = $_SERVER ['HTTP_CLIENT_IP'];
            else if (!empty ($_SERVER ['HTTP_X_FORWARDED_FOR'])) // to check ip is pass from proxy
                $this->ipAddress = $_SERVER ['HTTP_X_FORWARDED_FOR'];
            else
                $this->ipAddress = $_SERVER ['REMOTE_ADDR'];

            if (isset ($_SERVER ['HTTP_IF_NONE_MATCH']))
                $this->headers['If-None-Match'] = $_SERVER ['HTTP_IF_NONE_MATCH'];
            if (isset ($_SERVER ['HTTP_IF_MODIFIED_SINCE']))
                $this->headers['If-Modified-Since'] = $_SERVER ['HTTP_IF_MODIFIED_SINCE'];
            if (isset ($_SERVER ['HTTP_X_REQUESTED-WITH']))
                $this->headers['If-Modified-Since'] = $_SERVER ['HTTP_IF_MODIFIED_SINCE'];
            if (isset ($_SERVER ['HTTP_X_AUTH_TOKEN']))
                $this->headers['AuthToken'] = $_SERVER ['HTTP_X_AUTH_TOKEN'];
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

    public function headers() {
        return $this->headers;
    }

}