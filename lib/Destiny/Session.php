<?php

namespace Destiny;

use Destiny\Utils\Options;

class SessionInterface {
	
	/**
	 * The unique session Id
	 *
	 * @var string
	 */
	protected $sessionId = '';
	
	/**
	 * The session cookie
	 *
	 * @var SessionCookieInterface
	 */
	protected $sessionCookieInterface = null;
	
	/**
	 * The session authentication obj
	 *
	 * @var SessionAuthenticationCredentials
	 */
	protected $authenticationCredentials = null;

	/**
	 * Setup the session
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			Options::setOptions ( $this, $params );
		}
	}

	/**
	 * Start the session
	 *
	 * @return void
	 */
	public function start() {
		$sessionCookie = $this->getSessionCookieInterface ();
		session_set_cookie_params ( $sessionCookie->getLife (), $sessionCookie->getPath (), $sessionCookie->getDomain () );
		session_name ( $sessionCookie->getName () );
		session_start ();
		$this->setSessionId ( session_id () );
	}

	/**
	 * Deletes the session and recreates the session Id
	 *
	 * @return void
	 */
	public function destroy() {
		session_destroy ();
		session_regenerate_id ();
	}

	/**
	 * Get the authentication credentials if auth success
	 *
	 * @return \Destiny\SessionAuthenticationCredentials
	 */
	public function getAuthenticationCredentials() {
		return $this->authenticationCredentials;
	}

	/**
	 * Set the auth credentials
	 *
	 * @param unknown_type $sessionAuthenticationCredentials
	 */
	public function setAuthenticationCredentials(SessionAuthenticationCredentials $authenticationCredentials) {
		$this->authenticationCredentials = $authenticationCredentials;
	}

	/**
	 * Get the session cookie
	 *
	 * @return \Destiny\SessionCookieInterface
	 */
	public function getSessionCookieInterface() {
		return $this->sessionCookieInterface;
	}

	/**
	 * Set the session cookie
	 *
	 * @param unknown_type $sessionCookieInterface
	 */
	public function setSessionCookieInterface(SessionCookieInterface $sessionCookieInterface) {
		$this->sessionCookieInterface = $sessionCookieInterface;
	}

	/**
	 * Get the unique session Id
	 *
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}

	/**
	 * Set the session Id
	 *
	 * @param string $sessionId
	 */
	public function setSessionId($sessionId) {
		$this->sessionId = $sessionId;
	}

	/**
	 * Return all the session data
	 *
	 * @return array
	 */
	public function getData() {
		return $_SESSION;
	}

	/**
	 * Get a variable by name
	 *
	 * @param string $name
	 * @return mix
	 */
	public function get($name) {
		return (isset ( $_SESSION [$name] )) ? $_SESSION [$name] : null;
	}

	/**
	 * Set a variable by name
	 *
	 * @param string $name
	 * @param mix $value
	 */
	public function set($name, $value = null) {
		if ($value == null) {
			if (isset ( $_SESSION [$name] )) {
				unset ( $_SESSION [$name] );
			}
		} else {
			$_SESSION [$name] = $value;
		}
	}

}
class SessionCookieInterface {
	
	/**
	 * Cookie name
	 *
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Cookie life until expired
	 *
	 * @var int
	 */
	protected $life = 0;
	
	/**
	 * Cookie path
	 *
	 * @var string
	 */
	protected $path = '/';
	
	/**
	 * Cookie domain
	 *
	 * @var string
	 */
	protected $domain = '';

	/**
	 * Setup the cookie interface
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			Options::setOptions ( $this, $params );
		}
	}

	public function getName() {
		return $this->name;
	}

	public function getLife() {
		return $this->life;
	}

	public function getPath() {
		return $this->path;
	}

	public function getDomain() {
		return $this->domain;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setLife($life) {
		$this->life = $life;
	}

	public function setPath($path) {
		$this->path = $path;
	}

	public function setDomain($domain) {
		$this->domain = $domain;
	}

}
abstract class Session {
	
	/**
	 * The single session interface
	 *
	 * @var SessionInterface
	 */
	public static $instance = null;

	/**
	 * Get the session interface
	 *
	 * @return \Destiny\SessionInterface
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Create a new session interface
	 *
	 * @return \Destiny\SessionInterface
	 */
	public static function init(array $params) {
		self::$instance = new SessionInterface ();
		self::$instance->setSessionCookieInterface ( new SessionCookieInterface ( $params ) );
		self::$instance->start ();
		self::$instance->setAuthenticationCredentials ( new SessionAuthenticationCredentials ( self::$instance->getData () ) );
		return self::$instance;
	}

	/**
	 * Set and validate authentication creditials
	 *
	 * @param SessionAuthenticationCredentials $authenticationCredentials
	 */
	public static function setAuthCredentials(SessionAuthenticationCredentials $authenticationCredentials) {
		self::$instance->set ( 'email', $authenticationCredentials->getEmail () );
		self::$instance->set ( 'displayName', $authenticationCredentials->getDisplayName () );
		self::$instance->set ( 'username', $authenticationCredentials->getUserName () );
		self::$instance->set ( 'userId', $authenticationCredentials->getUserId () );
		self::$instance->set ( 'country', $authenticationCredentials->getCountry () );
		self::$instance->set ( 'roles', $authenticationCredentials->getRoles () );
		self::$instance->set ( 'authorized', $authenticationCredentials->getAuthorized () );
		self::$instance->setAuthenticationCredentials ( $authenticationCredentials );
	}

	/**
	 * Get the current authenticated session credentials
	 *
	 * @return \Destiny\SessionAuthenticationCredentials
	 */
	public static function getAuthCredentials() {
		return self::getInstance ()->getAuthenticationCredentials ();
	}

	/**
	 * Destroys the current session
	 *
	 * @return void
	 */
	public static function destroy() {
		self::getInstance ()->destroy ();
	}

	/**
	 * Get a session variable
	 *
	 * @param string $name
	 * @return mix
	 */
	public static function get($name) {
		return self::getInstance ()->get ( $name );
	}

	/**
	 * Set a session variable
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value = null) {
		self::getInstance ()->set ( $name, $value );
	}

	/**
	 * Check if the creditials has a specific role
	 *
	 * @param string $roleId
	 * @return boolean
	 */
	public static function hasRole($roleId) {
		$authCreds = self::getAuthCredentials ();
		if (! empty ( $authCreds ) && $authCreds->hasRole ( $roleId )) {
			return true;
		}
		return false;
	}

	/**
	 * Return if the creds have been authorized
	 *
	 * @return boolean
	 */
	public static function authorized() {
		$authCreds = self::getAuthCredentials ();
		return $authCreds->getAuthorized ();
	}

}
class SessionAuthenticationCredentials {
	
	/**
	 * Whether or not these credentials have been authorized
	 *
	 * @var boolean
	 */
	protected $authorized = false;
	
	/**
	 * The authed creds email
	 *
	 * @var string
	 */
	protected $displayName = '';
	
	/**
	 * The authed creds Id
	 *
	 * @var string int
	 */
	protected $userId = '';
	
	/**
	 * The authed creds screen name
	 *
	 * @var string
	 */
	protected $username = '';
	
	/**
	 * The authed creds email
	 *
	 * @var string
	 */
	protected $email = '';
	
	/**
	 * The authed creds country
	 *
	 * @var string
	 */
	protected $country = '';
	
	/**
	 * The creds roles
	 *
	 * @var string
	 */
	protected $roles = array ();

	/**
	 * Setup credentials
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			if (isset ( $params ['userId'] ) && ! empty ( $params ['userId'] )) {
				$this->setUserId ( $params ['userId'] );
			}
			if (isset ( $params ['displayName'] ) && ! empty ( $params ['displayName'] )) {
				$this->setDisplayName ( $params ['displayName'] );
			}
			if (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) {
				$this->setUsername ( $params ['username'] );
			}
			if (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) {
				$this->setEmail ( $params ['email'] );
			}
			if (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) {
				$this->setCountry ( $params ['country'] );
			}
			if (isset ( $params ['roles'] ) && ! empty ( $params ['roles'] )) {
				if (! is_array ( $params ['roles'] )) {
					$params ['roles'] = explode ( ',', $params ['roles'] );
				}
				$this->setRoles ( $params ['roles'] );
			}
			if (isset ( $params ['authorized'] ) && ! empty ( $params ['authorized'] ) && $params ['authorized'] == '1') {
				$this->setAuthorized ( true );
			}
		}
	}

	/**
	 * Validate these credentials are valid
	 *
	 * @return boolean
	 */
	public function valid() {
		return true;
	}

	/**
	 * Get the screen name
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * Set the display name
	 *
	 * @param string $displayName
	 */
	public function setDisplayName($displayName) {
		$this->displayName = $displayName;
	}

	/**
	 * Get the username
	 *
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set the username
	 *
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Get the email
	 *
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Set the email
	 *
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Get the roles
	 *
	 * @return string
	 */
	public function getRoles() {
		return $this->roles;
	}

	/**
	 * Set the roles
	 *
	 * @param array $roles
	 */
	public function setRoles(array $roles) {
		$this->roles = $roles;
	}

	/**
	 * Add a roles
	 *
	 * @param string $role
	 */
	public function addRoles($role) {
		if (is_array ( $role )) {
			$this->roles [] += $role;
		} else {
			$this->roles [] = $role;
		}
	}

	/**
	 * Check if this auth has a specific role
	 *
	 * @param string $roleId
	 */
	public function hasRole($roleId) {
		foreach ( $this->roles as $role ) {
			if (strcasecmp ( $role, $roleId ) === 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get authorized
	 *
	 * @return boolean
	 */
	public function getAuthorized() {
		return $this->authorized;
	}

	/**
	 * Set authorized
	 *
	 * @param bool $authorized
	 */
	public function setAuthorized($authorized) {
		$this->authorized = $authorized;
	}

	/**
	 * Get the country code
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Set the country code
	 *
	 * @param unknown_type $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Get the user id
	 *
	 * @return string
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Set the user Id
	 *
	 * @param string $userId
	 */
	public function setUserId($userId) {
		$this->userId = $userId;
	}

}