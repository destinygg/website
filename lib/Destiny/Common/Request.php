<?php
namespace Destiny\Common;

class Request {

    const HEADER_CONTENT_TYPE      = 'Content-Type';
    const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
    const HEADER_IF_NONE_MATCH     = 'If-None-Match';
    const HEADER_REQUESTED_WITH    = 'Requested-With';
    const HEADER_AUTH_TOKEN        = 'AuthToken';

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

    /**
     * @var array
     */
    private $allheaders;

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

            foreach ($_SERVER as $k => $v) {
                if(substr($k, 0, 6 ) === 'HTTP_X') {
                    $this->allheaders[$k] = $v;
                }
            }

            if (isset ($_SERVER['HTTP_IF_NONE_MATCH']))
                $this->headers[self::HEADER_IF_NONE_MATCH] = $_SERVER['HTTP_IF_NONE_MATCH'];
            if (isset ($_SERVER['HTTP_IF_MODIFIED_SINCE']))
                $this->headers[self::HEADER_IF_MODIFIED_SINCE] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            if (isset ($_SERVER['HTTP_X_REQUESTED_WITH']))
                $this->headers[self::HEADER_REQUESTED_WITH] = $_SERVER['HTTP_X_REQUESTED_WITH'];
            if (isset ($_SERVER['HTTP_X_AUTH_TOKEN']))
                $this->headers[self::HEADER_AUTH_TOKEN] = $_SERVER['HTTP_X_AUTH_TOKEN'];
            if (isset($_SERVER['HTTP_CONTENT_TYPE']))
                $this->headers[self::HEADER_CONTENT_TYPE] = $_SERVER['HTTP_CONTENT_TYPE'];
        }
    }

    public function getBody(){
        return file_get_contents('php://input');
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

    public function header($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    public function rawheader($name) {
        $name = 'HTTP_' . str_replace('-', '_', strtoupper($name));
        return isset($this->allheaders[$name]) ? $this->allheaders[$name] : null;
    }

}