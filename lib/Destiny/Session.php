<?php

namespace Destiny;

use Destiny\Utils\Options;

class SessionInstance {
	
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
	 * @todo this does a mix of things, need to clean-up
	 * @return void
	 */
	public function start() {
		$sessionCookie = $this->getSessionCookieInterface ();
		session_set_cookie_params ( $sessionCookie->getLife (), $sessionCookie->getPath (), $sessionCookie->getDomain () );
		session_name ( $sessionCookie->getName () );
		session_start ();
		$this->setSessionId ( session_id () );
		// weird
		$authCreds = $this->getAuthenticationCredentials ();
		$authCreds->setCredentials ( $this->getData () );
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
	public function setAuthenticationCredentials(SessionAuthenticationCredentials $authCreds) {
		$this->authenticationCredentials = $authCreds;
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
	 * Set the creds
	 *
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			$this->setCredentials ( $params );
		}
	}

	/**
	 * Set all the credentials at once
	 *
	 * @param array $params
	 */
	public function setCredentials(array $params) {
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
				$this->setRoles ( array_unique ( $params ['roles'] ) );
			}
			if (isset ( $params ['authorized'] ) && ! empty ( $params ['authorized'] ) && $params ['authorized'] == '1') {
				$this->setAuthorized ( true );
			}
		}
	}

	/**
	 * Set all the credentials at once
	 *
	 * @param array $params
	 */
	public function getCredentials() {
		return array (
				'email' => $this->getEmail (),
				'displayName' => $this->getDisplayName (),
				'username' => $this->getUserName (),
				'userId' => $this->getUserId (),
				'country' => $this->getCountry (),
				'roles' => $this->getRoles (),
				'authorized' => $this->getAuthorized () 
		);
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
			for($i = 0; $i < count ( $role ); ++ $i) {
				if (! in_array ( $role [$i], $this->roles )) {
					$this->roles [] = $role [$i];
				}
			}
		} elseif (is_string ( $role ) && ! in_array ( $role, $this->roles )) {
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
abstract class Session {
	
	/**
	 * The single session interface
	 *
	 * @var SessionInstance
	 */
	public static $instance = null;

	/**
	 * Return the instances unique session Id
	 *
	 * @return string
	 */
	public static function getSessionId() {
		return self::instance ()->getSessionId ();
	}

	/**
	 * Get the session interface
	 *
	 * @return \Destiny\SessionInstance
	 */
	public static function instance() {
		return self::$instance;
	}

	/**
	 * Set the session instance
	 *
	 * @param SessionInstance $session
	 */
	public static function setInstance(SessionInstance $session) {
		self::$instance = $session;
		return self::$instance;
	}

	/**
	 * Get the current authenticated session credentials
	 *
	 * @return \Destiny\SessionAuthenticationCredentials
	 */
	public static function getAuthCreds() {
		return self::instance ()->getAuthenticationCredentials ();
	}

	/**
	 * Set and validate authentication creditials
	 *
	 * @param SessionAuthenticationCredentials $authenticationCredentials
	 */
	public static function setAuthCreds(SessionAuthenticationCredentials $authCreds) {
		$session = self::instance ();
		$session->setAuthenticationCredentials ( $authCreds );
		$params = $session->getAuthenticationCredentials ()->getCredentials ();
		foreach ( $params as $name => $value ) {
			$session->set ( $name, $value );
		}
	}

	/**
	 * Destroys the current session
	 *
	 * @return void
	 */
	public static function destroy() {
		$session = self::instance ();
		$session->destroy ();
	}

	/**
	 * Get a session variable
	 *
	 * @param string $name
	 * @return mix
	 */
	public static function get($name) {
		return self::instance ()->get ( $name );
	}

	/**
	 * Set a session variable
	 *
	 * @param string $name
	 * @param string $value
	 */
	public static function set($name, $value = null) {
		self::instance ()->set ( $name, $value );
	}

	/**
	 * Check if the creditials has a specific role
	 *
	 * @param string $roleId
	 * @return boolean
	 */
	public static function hasRole($roleId) {
		$authCreds = self::getAuthCreds ();
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
		$authCreds = self::getAuthCreds ();
		return $authCreds->getAuthorized ();
	}

}