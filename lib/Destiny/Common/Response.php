<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Http;

class Response {

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var int
     */
    private $status = Http::STATUS_OK;

    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $location;

    public function __construct($status = Http::STATUS_OK, $body = '') {
        $this->status = $status;
        $this->body = $body;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function addHeader($name, $value) {
        $this->headers [] = [$name,$value];
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
    }

}