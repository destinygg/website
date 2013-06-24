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
	}

	/**
	 * Deletes the session and recreates the session Id
	 *
	 * @return void
	 */
	public function destroy() {
		session_destroy ();
		session_regenerate_id ();
		$_SESSION = array ();
		$this->setSessionId ( session_id () );
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
	 * Check if a variable is empty
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isEmpty($name) {
		$value = (isset ( $_SESSION [$name] )) ? $_SESSION [$name] : null;
		return empty ( $value );
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
		if ($value === null) {
			if (isset ( $_SESSION [$name] )) {
				$value = $_SESSION [$name];
				unset ( $_SESSION [$name] );
			}
		} else {
			$_SESSION [$name] = $value;
		}
		return $value;
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
	 * The authentication provider used for this use
	 *
	 * @var string
	 */
	protected $authProvider = '';
	
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
	 * The users status
	 *
	 * @var string
	 */
	protected $userStatus = '';
	
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
	 * A list of features
	 *
	 * @var array
	 */
	protected $features = array ();

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
			if (isset ( $params ['username'] ) && ! empty ( $params ['username'] )) {
				$this->setUsername ( $params ['username'] );
			}
			if (isset ( $params ['email'] ) && ! empty ( $params ['email'] )) {
				$this->setEmail ( $params ['email'] );
			}
			if (isset ( $params ['country'] ) && ! empty ( $params ['country'] )) {
				$this->setCountry ( $params ['country'] );
			}
			if (isset ( $params ['authProvider'] ) && ! empty ( $params ['authProvider'] )) {
				$this->setAuthProvider ( $params ['authProvider'] );
			}
			if (isset ( $params ['userStatus'] ) && ! empty ( $params ['userStatus'] )) {
				$this->setUserStatus ( $params ['userStatus'] );
			}
			if (isset ( $params ['features'] ) && ! empty ( $params ['features'] ) && is_array ( $params ['features'] )) {
				$this->setFeatures ( $params ['features'] );
			}
			if (isset ( $params ['roles'] ) && ! empty ( $params ['roles'] ) && is_array ( $params ['roles'] )) {
				$this->setRoles ( array_unique ( $params ['roles'] ) );
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
				'username' => $this->getUserName (),
				'userId' => $this->getUserId (),
				'userStatus' => $this->getUserStatus (),
				'country' => $this->getCountry (),
				'roles' => $this->getRoles (),
				'authProvider' => $this->getAuthProvider (),
				'features' => $this->getFeatures () 
		);
	}

	/**
	 * Return the credentials hash
	 *
	 * @return string
	 */
	public function getHash() {
		return sprintf ( "%u", crc32 ( serialize ( $this->getCredentials () ) ) );
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getRoles() {
		return $this->roles;
	}

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
		} elseif (! in_array ( $role, $this->roles )) {
			$this->roles [] = $role;
		}
	}

	/**
	 * Check if this auth has a specific role
	 *
	 * @param int $roleId
	 */
	public function hasRole($roleId) {
		foreach ( $this->roles as $role ) {
			if ($role == $roleId) {
				return true;
			}
		}
		return false;
	}

	public function getCountry() {
		return $this->country;
	}

	public function setCountry($country) {
		$this->country = $country;
	}

	public function getUserId() {
		return $this->userId;
	}

	public function setUserId($userId) {
		$this->userId = $userId;
	}

	public function getAuthProvider() {
		return $this->authProvider;
	}

	public function setAuthProvider($authProvider) {
		$this->authProvider = $authProvider;
	}

	public function getUserStatus() {
		return $this->userStatus;
	}

	public function setUserStatus($userStatus) {
		$this->userStatus = $userStatus;
	}

	public function getFeatures() {
		return $this->features;
	}

	public function setFeatures(array $features) {
		$this->features = $features;
	}

	/**
	 * Check if this auth has a specific feature
	 *
	 * @param int $roleId
	 */
	public function hasFeature($featureId) {
		foreach ( $this->features as $feature ) {
			if ($feature == $featureId) {
				return true;
			}
		}
		return false;
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
	public static function updateAuthCreds(SessionAuthenticationCredentials $authCreds) {
		$session = self::instance ();
		$session->setAuthenticationCredentials ( $authCreds );
		$params = $authCreds->getCredentials ();
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
	 * Set a session variable, if the value is NULL, unset the variable
	 * Returns the variable like a getter, but does the setter stuff too
	 *
	 * @param string $name
	 * @param string $value
	 * @return mix
	 */
	public static function set($name, $value = null) {
		return self::instance ()->set ( $name, $value );
	}

	/**
	 * Check if the creditials has a specific role
	 *
	 * @param int $roleId
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
	 * Check if the creditials has a specific feature
	 *
	 * @param int $featureId
	 * @return boolean
	 */
	public static function hasFeature($featureId) {
		$authCreds = self::getAuthCreds ();
		if (! empty ( $authCreds ) && $authCreds->hasFeature ( $featureId )) {
			return true;
		}
		return false;
	}

}