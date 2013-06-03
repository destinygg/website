<?php
namespace Destiny\Api;

use Destiny\Config;
use Destiny\Mimetype;
use Destiny\Utils\Http;
use Destiny\Utils\Options;

/**
 * Used simply to retrieve, cache and return HTTP data via URL
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
	 * The caching object
	 *
	 * @var DestinyFileCache
	 */
	public $cache = null;
	
	/**
	 * Cache life
	 *
	 * @var int
	 */
	public $life = - 1;
	
	/**
	 * Wether or not to check for cache headers
	 *
	 * @var boolean
	 */
	public $checkIfModified = false;
	
	/**
	 * The reponse code
	 *
	 * @var int
	 */
	public $responseCode = Http::STATUS_OK;
	
	/**
	 * Data to post
	 * @var array
	 */
	public $postData = null;
	
	
	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct(array $args = null) {
		Options::setOptions ( $this, $args );
		// This config line shouldnt be here
		if ($this->cache == null) {
			$id = ((isset ( $args ['id'] )) ? $args ['id'] : md5 ( "$this->url" . "$this->life" ));
			$cacheId = ((isset ( $args ['tag'] )) ? $args ['tag'] : '') . '.' . $id;
			$this->cache = new Config::$a ['cache'] ['engine'] ( array (
				'filename' => Config::$a ['cache'] ['path'] . $cacheId,
				'life' => $this->life 
			) );
		}
		if (! empty ( $this->url )) {
			$this->response = $this->consume ($args);
		}
	}

	public function consume(array $args = null) {
		if ((isset ( $args ['cacheFirst'] ) && $args ['cacheFirst'] == true) && $this->cache->exists () == true) {
			return $this->stringToDataType ( $this->cache->read () );
		}
		if ($this->life < 0 || $this->cache->cached () == false) {
			try {
				// Set the cache, so that if a real issue or stall happens after this point
				// subsequent request will not do the same
				if ($this->life > 0 && $this->cache->exists () == false) {
					$this->cache->write ( '' );
				}
				// Update the modified time before the fetch begins
				$this->cache->updateModifiedTime ();
				
				// Reset the time limit for each request
				set_time_limit ( Config::$a ['env'] ['max_execution_time'] );
				
				$response = $this->fetch ( $this->url );
				if ($this->responseCode != Http::STATUS_OK) {
					throw new \Exception ( "Error: " . $this->responseCode . ' Data: ' . $response );
				}
				$response = $this->stringToDataType ( $response );
				if (! empty ( $this->onfetch ) && is_callable ( $this->onfetch )) {
					$response = call_user_func ( $this->onfetch, $response, $this->params );
				}
				if ($this->life > 0) {
					$this->cache->write ( $this->dataTypeToString ( $response ) );
				}
			} catch ( \Exception $e ) {
				if ($this->life < 0) {
					throw $e;
				}
				// Update modified time so error'ed requests do not immediately que up, but wait the expiration time
				if (! $this->cache->exists ()) {
					$this->cache->write ( '' );
				}
				if ($this->checkIfModified && $this->responseCode == Http::STATUS_NOT_MODIFIED) {
					Http::checkIfModifiedSince ( $this->cache->getLastModified(), true );
				}
				$this->cache->updateModifiedTime ();
				$response = $this->stringToDataType ( $this->cache->read () );
			}
		} else {
			if ($this->checkIfModified && $this->cache->cached ()) {
				Http::checkIfModifiedSince ( $this->cache->getLastModified(), true );
			}
			$response = $this->stringToDataType ( $this->cache->read () );
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
		} else {
			if ($this->checkIfModified && $this->life > 0 && $this->cache->getLastModified() > 0) {
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
						Http::HEADER_IF_MODIFIED_SINCE . ': ' . gmdate ( 'r', $this->cache->getLastModified() ) 
				) );
			}
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
					return json_decode ( $str );
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
		return $data;
	}

	public function __toString() {
		return $this->dataTypeToString ( $this->getResponse () );
	}

	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * @return DestinyFileCache
	 */
	public function getCache(){
		return $this->cache;
	}

}