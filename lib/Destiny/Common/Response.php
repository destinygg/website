<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Http;

class Response {

    private $headers = [];
    private $status = Http::STATUS_OK;
    private $location = '';
    private $body;

    public function __construct(int $status = Http::STATUS_OK, $body = null) {
        $this->status = $status;
        $this->body = $body;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function setHeaders(array $headers) {
        $this->headers = $headers;
    }

    public function addHeader(string $name, string $value) {
        $this->headers [] = [$name, $value];
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function setStatus(int $status) {
        $this->status = $status;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function setLocation(string $location) {
        $this->location = $location;
    }

}