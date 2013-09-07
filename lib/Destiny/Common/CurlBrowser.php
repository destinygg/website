<?php
namespace Destiny\Common;

use Destiny\Common\Config;
use Destiny\Common\MimeType;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Options;
use Destiny\Common\Application;
use Psr\Log\LoggerInterface;
use Destiny\Exception;

/**
 * Used simply to retrieve HTTP data via a curl URL request
 */
class CurlBrowser {
	
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
	public $timeout = 0;
	
	/**
	 * Maximum time to wait for a valid connection
	 *
	 * @var int
	 */
	public $connectTimeout = 0;
	
	/**
	 * The data content type
	 *
	 * @var string
	 */
	public $contentType = MimeType::TEXT;
	
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
	 * The CURL request headers
	 *
	 * @var array
	 */
	protected $headers = array ();
	
	/**
	 * The CURL verify peer flag
	 *
	 * @var boolean
	 */
	protected $verifyPeer = true;

	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct(array $args = null) {
		$this->setLogger ( Application::instance ()->getLogger () );
		Options::setOptions ( $this, Config::$a ['curl'] );
		Options::setOptions ( $this, $args );
		if (! empty ( $this->url )) {
			$this->response = $this->browse ();
		}
	}

	/**
	 * Fetch the url, run the onfetch function
	 *
	 * @return array
	 */
	protected function browse() {
		$response = $this->fetch ( $this->getUrl () );
		if ($this->responseCode == Http::STATUS_OK) {
			$response = $this->stringToDataType ( $response );
			$onFetch = $this->getOnfetch ();
			if (! empty ( $onFetch ) && is_callable ( $onFetch )) {
				$response = call_user_func ( $onFetch, $response, $this->getParams () );
			}
		}
		return $response;
	}

	/**
	 * Do a curl request to a specific url
	 *
	 * @param string $url
	 * @return mixed
	 */
	protected function fetch($url) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, $this->getVerifyPeer () );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, $this->getTimeout () );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, $this->getConnectTimeout () );
		$postData = $this->getPostData ();
		if (! empty ( $postData )) {
			curl_setopt ( $curl, CURLOPT_POST, true );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $postData );
		}
		$headers = $this->getHeaders ();
		if (! empty ( $headers )) {
			curl_setopt ( $curl, CURLOPT_HTTPHEADER, $headers );
		}
		$data = curl_exec ( $curl );
		$info = curl_getinfo ( $curl );
		$this->logger->debug ( sprintf ( 'Curl.HTTP(%s): %s %s', $info ['http_code'], \Destiny\Common\Utils\String::strictUTF8 ( $url ), json_encode ( $postData ) ) );
		$this->responseCode = intval ( $info ['http_code'] );
		return $data;
	}

	/**
	 * Converts the response string into its data type
	 *
	 * @param string $str
	 * @return mixed
	 */
	private function stringToDataType($str) {
		if (is_string ( $str )) {
			switch ($this->contentType) {
				case MimeType::JSON :
					return json_decode ( $str, true );
					break;
			}
		}
		return $str;
	}

	/**
	 * Converts the data type into a string
	 *
	 * @param array $data
	 * @return string
	 */
	private function dataTypeToString($data) {
		if (is_array ( $data ) || is_object ( $data )) {
			switch ($this->contentType) {
				case MimeType::JSON :
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

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function getTimeout() {
		return $this->timeout;
	}

	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	public function getConnectTimeout() {
		return $this->connectTimeout;
	}

	public function setConnectTimeout($connectTimeout) {
		$this->connectTimeout = $connectTimeout;
	}

	public function getContentType() {
		return $this->contentType;
	}

	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

	public function getOnfetch() {
		return $this->onfetch;
	}

	public function setOnfetch($onfetch) {
		$this->onfetch = $onfetch;
	}

	public function getBeforefetch() {
		return $this->beforefetch;
	}

	public function setBeforefetch($beforefetch) {
		$this->beforefetch = $beforefetch;
	}

	public function getParams() {
		return $this->params;
	}

	public function setParams($params) {
		$this->params = $params;
	}

	public function getVerifyPeer() {
		return $this->verifyPeer;
	}

	public function setVerifyPeer($verifyPeer) {
		$this->verifyPeer = $verifyPeer;
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	public function getResponseCode() {
		return $this->responseCode;
	}

	public function setResponseCode($responseCode) {
		$this->responseCode = $responseCode;
	}

	public function getPostData() {
		return $this->postData;
	}

	public function setPostData(array $postData) {
		$this->postData = $postData;
	}

	public function getLogger() {
		return $this->logger;
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	public function setResponse($response) {
		$this->response = $response;
	}

	public function isPost() {
		return (empty ( $this->postData )) ? false : true;
	}

}