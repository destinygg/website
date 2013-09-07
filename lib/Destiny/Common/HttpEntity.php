<?php
namespace Destiny\Common;

class HttpEntity {
	
	private $headers = array ();
	private $status;
	private $body;

	public function __construct($status, $body = null) {
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
		$this->headers [] = array($name, $value);
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

}