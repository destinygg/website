<?php
namespace Destiny\Common;

use Destiny\Common\Utils\String\Params;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Options;

/**
 * Dirt simple oauth implementation for some standard apis
 * also dirty...
 */
class OAuthClient {
	
	/**
	 * The apps client_id registered with the auth provider
	 *
	 * @var string
	 */
	protected $clientId = '';
	
	/**
	 * The apps client_secret registered with the auth provider
	 *
	 * @var string
	 */
	protected $clientSecret = '';
	
	/**
	 * Some oauth implementations use different prefixes when specifying the Authorization: header.
	 * Twitch uses 'OAuth', google uses 'Bearer'
	 *
	 * @var string
	 */
	protected $headerTokenName = 'Bearer';

	/**
	 * Constructor
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			Options::setOptions ( $this, $params );
		}
	}

	/**
	 * Request a auth token from twitch
	 *
	 * @param string $code
	 * @param string $url
	 * @param string $redirect
	 * @throws AppException
	 * @return string false
	 */
	public function fetchAccessToken($code, $url, $redirect) {
		$post = array ();
		$post ['code'] = $code;
		$post ['redirect_uri'] = $redirect;
		$post ['grant_type'] = 'authorization_code';
		$clientId = $this->getClientId ();
		if (! empty ( $clientId )) {
			$post ['client_id'] = $clientId;
		}
		$clientSecret = $this->getClientSecret ();
		if (! empty ( $clientSecret )) {
			$post ['client_secret'] = $clientSecret;
		}
		$curlBrowser = new CurlBrowser ( array (
			'url' => $url,
			'contentType' => MimeType::JSON,
			'postData' => $post 
		) );
		if ($curlBrowser->getResponseCode () != 200) {
			throw new AppException ( 'Request access token failed' );
		}
		$data = $curlBrowser->getResponse ();
		if (empty ( $data ) || ! isset ( $data ['access_token'] ) || empty ( $data ['access_token'] )) {
			throw new AppException ( 'Request for access token failed' );
		}
		return $data ['access_token'];
	}

	/**
	 * Request a user from the API
	 *
	 * @param string $token
	 * @return array
	 */
	public function fetchUserInfo($token, $url) {
		$curlBrowser = new CurlBrowser ( array (
			'url' => $url,
			'contentType' => MimeType::JSON,
			'headers' => array (
				'Authorization: ' . $this->getHeaderTokenName () . ' ' . $token 
			) 
		) );
		if ($curlBrowser->getResponseCode () != 200) {
			throw new AppException ( 'Request for user failed' );
		}
		return $curlBrowser->getResponse ();
	}

	/**
	 * Get the authorization request endpoint
	 *
	 * @param string $url
	 * @param string $redirect
	 * @param string $scope
	 * @return string
	 */
	public function getAuthorisationEndPoint($url, $redirect, $scope, array $params = array()) {
		$params ['response_type'] = 'code';
		$params ['client_id'] = urlencode ( $this->getClientId () );
		$params ['redirect_uri'] = urlencode ( $redirect );
		$params ['scope'] = $scope;
		return $url . '?' . Params::params ( $params );
	}

	public function getClientId() {
		return $this->clientId;
	}

	public function setClientId($clientId) {
		$this->clientId = $clientId;
	}

	public function getClientSecret() {
		return $this->clientSecret;
	}

	public function setClientSecret($clientSecret) {
		$this->clientSecret = $clientSecret;
	}

	public function getHeaderTokenName() {
		return $this->headerTokenName;
	}

	public function setHeaderTokenName($headerTokenName) {
		$this->headerTokenName = $headerTokenName;
	}

}