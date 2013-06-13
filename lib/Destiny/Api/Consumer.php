<?php

namespace Destiny\Api;

use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Utils\Http;
use Destiny\Utils\Options;
use Destiny\Application;
use Psr\Log\LoggerInterface;
use Destiny\AppException;

/**
 * Used simply to retrieve HTTP data via a curl URL request
 */
class Consumer {
	
	/**
	 * The url to call
	 *
	 * @var string
	 */
	public $url = '';
	
	/**
	 * The response
	 *
	 * @var mix | string
	 */
	public $response = null;
	
	/**
	 * Maximum time to wait for response to complete
	 *
	 * @var int
	 */
	public $timeout = 10;
	
	/**
	 * Maximum time to wait for a valid connection
	 *
	 * @var int
	 */
	public $connectTimeout = 5;
	
	/**
	 * The data content type
	 *
	 * @var string
	 */
	public $contentType = Mimetype::TEXT;
	
	/**
	 * OnFetch method
	 *
	 * @var function
	 */
	public $onfetch = null;
	
	/**
	 * BeforeFetch method
	 *
	 * @var function
	 */
	public $beforefetch = null;
	
	/**
	 * Argument to be passed into feth method
	 *
	 * @var mix
	 */
	public $params = null;
	
	/**
	 * The reponse code
	 *
	 * @var int
	 */
	public $responseCode = Http::STATUS_OK;
	
	/**
	 * Data to post
	 *
	 * @var array
	 */
	public $postData = null;
	
	/**
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct(array $args = null) {
		Options::setOptions ( $this, $args );
		$this->logger = Application::getInstance ()->getLogger ();
		if (! empty ( $this->url )) {
			$this->response = $this->consume ( $args );
		}
	}

	public function consume(array $args = null) {
		$response = $this->fetch ( $this->url );
		if ($this->responseCode != Http::STATUS_OK) {
			throw new AppException ( "Error: " . $this->responseCode . ' Data: ' . $response );
		}
		$this->logger->debug ( sprintf ( 'Curl.HTTP(%s): %s', $this->responseCode, \Destiny\Utils\String::strictUTF8 ( $this->url ) ) );
		$response = $this->stringToDataType ( $response );
		if (! empty ( $this->onfetch ) && is_callable ( $this->onfetch )) {
			$response = call_user_func ( $this->onfetch, $response, $this->params );
		}
		return $response;
	}

	protected function fetch($url) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout );
		if (! empty ( $this->postData )) {
			curl_setopt ( $curl, CURLOPT_POST, true );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $this->postData );
		}
		$data = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		$this->responseCode = intval ( $info ['http_code'] );
		return $data;
	}

	private function stringToDataType($str) {
		if (is_string ( $str )) {
			switch ($this->contentType) {
				case Mimetype::JSON :
					return json_decode ( $str, true );
					break;
			}
		}
		return $str;
	}

	private function dataTypeToString($data) {
		if (is_array ( $data ) || is_object ( $data )) {
			switch ($this->contentType) {
				case Mimetype::JSON :
					return json_encode ( $data );
					break;
			}
		}
		return "$data";
	}

	public function __toString() {
		return $this->dataTypeToString ( $this->getResponse () );
	}

	public function getResponse() {
		return $this->response;
	}

}