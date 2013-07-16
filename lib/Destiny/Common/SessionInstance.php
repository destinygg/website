<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Options;

class SessionInstance {
	
	/**
	 * Wether or not the session has been started
	 *
	 * @var boolean
	 */
	protected $started = false;
	
	/**
	 * The unique session Id
	 *
	 * @var string
	 */
	protected $sessionId = '';
	
	/**
	 * The session cookie
	 *
	 * @var SessionCookie
	 */
	protected $sessionCookie = null;
	
	/**
	 * The session authentication
	 *
	 * @var SessionCredentials
	 */
	protected $credentials = null;
	
	/**
	 * A list of callable credential handlers
	 *
	 * @var array
	 */
	protected $credentialHandlers = array ();
	
	/**
	 * A list of callable cleanup handlers
	 *
	 * @var array
	 */
	protected $cleanupHandlers = array ();

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
	 * Start the session, return true if the session was started otherwise false
	 *
	 * @todo this does a mix of things, need to clean-up
	 * @return boolean
	 */
	public function start() {
		$this->started = session_start ();
		$this->setSessionId ( session_id () );
		$credentials = $this->getCredentials ();
		$credentials->setData ( $this->getData () );
		return $this->started;
	}

	/**
	 * Regenerates the session id
	 *
	 * @param boolean $delete Delete the old associated session file
	 * @return boolean
	 */
	public function renew($delete = true) {
		if ($this->isStarted () || $this->start ()) {
			session_regenerate_id ( $delete );
			$this->setSessionId ( session_id () );
			return true;
		}
		return false;
	}

	/**
	 * Deletes the session and recreates the session Id
	 *
	 * @return void
	 */
	public function destroy() {
		$cookie = $this->getSessionCookie ();
		$cookie->clearCookie ();
		session_destroy ();
		$_SESSION = array ();
		session_regenerate_id ( false );
	}

	/**
	 * Get the authentication credentials if auth success
	 *
	 * @return SessionCredentials
	 */
	public function getCredentials() {
		return $this->credentials;
	}

	/**
	 * Set the auth credentials
	 *
	 * @param unknown_type $credentials
	 */
	public function setCredentials(SessionCredentials $credentials) {
		$this->credentials = $credentials;
	}

	/**
	 * Get the session cookie
	 *
	 * @return \Destiny\SessionCookie
	 */
	public function getSessionCookie() {
		return $this->sessionCookie;
	}

	/**
	 * Set the session cookie
	 *
	 * @param unknown_type $sessionCookie
	 */
	public function setSessionCookie(SessionCookie $sessionCookie) {
		$this->sessionCookie = $sessionCookie;
		session_set_cookie_params ( $sessionCookie->getLife (), $sessionCookie->getPath (), $sessionCookie->getDomain () );
		session_name ( $sessionCookie->getName () );
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

	/**
	 * Returns true if the session has been stared, false otherwise
	 *
	 * @return boolean
	 */
	public function isStarted() {
		return $this->started;
	}

	/**
	 * Prepends a handler to the handler stack
	 *
	 * @param callabel $fn
	 */
	public function addCredentialHandler($fn) {
		array_unshift ( $this->credentialHandlers, $fn );
	}

	/**
	 * Run all the credential managers
	 *
	 * @return void
	 */
	public function executeCredentialHandlers(SessionCredentials $credentials) {
		foreach ( $this->credentialHandlers as $handler ) {
			call_user_func ( $handler, $this, $credentials );
		}
	}

	/**
	 * Prepends a handler to the handler stack
	 *
	 * @param callabel $fn
	 */
	public function addCleanupHandler($fn) {
		array_unshift ( $this->cleanupHandlers, $fn );
	}

	/**
	 * Run all the cleanup handlers
	 *
	 * @return void
	 */
	public function executeCleanupHandlers() {
		foreach ( $this->cleanupHandlers as $handler ) {
			call_user_func ( $handler, $this );
		}
	}

}
class SessionCookie {
	
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

	/**
	 * Get the session cookie id
	 *
	 * @return string NULL
	 */
	public function getCookie() {
		if (isset ( $_COOKIE [$this->name] )) {
			return $_COOKIE [$this->name];
		}
		return null;
	}

	/**
	 * Clears the session cookie
	 */
	public function clearCookie() {
		if (isset ( $_COOKIE [$this->name] )) {
			unset ( $_COOKIE [$this->name] );
		}
		setcookie ( $this->name, '', time () - 3600, $this->getPath (), $this->getDomain () );
	}

}
class SessionCredentials {
	
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
	 * Create a session credentials instance
	 * @param array $params
	 */
	public function __construct(array $params = null) {
		if (! empty ( $params )) {
			$this->setData ( $params );
		}
	}

	/**
	 * Set all the credentials at once
	 *
	 * @param array $params
	 */
	public function setData(array $params) {
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
				$this->setFeatures ( array_unique ( $params ['features'] ) );
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
	public function getData() {
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
	 * Checks whether or not the credentials are populated and valid
	 * username, userId and userStatus must be set and not empty
	 *
	 * @return boolean
	 */
	public function isValid() {
		$data = $this->getData ();
		if (empty ( $data ['userId'] ) && intval ( $data ['userId'] ) > 0) {
			return false;
		}
		if (empty ( $data ['username'] )) {
			return false;
		}
		if (empty ( $data ['userStatus'] )) {
			return false;
		}
		return true;
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
	 * Add roles
	 *
	 * @param array|string $role
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
	 * Remove a role
	 *
	 * @param string $role
	 */
	public function removeRole($role) {
		for($i = 0; $i < count ( $this->roles ); ++ $i) {
			if ($this->roles [$i] == $role) {
				unset ( $this->roles [$i] );
				break;
			}
		}
	}

	/**
	 * Check if this auth has a specific role
	 *
	 * @param int $roleId
	 */
	public function hasRole($roleId) {
		foreach ( $this->roles as $role ) {
			if (strcasecmp ( $role, $roleId ) === 0) {
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

	/**
	 * Add user features
	 *
	 * @param array|string $features
	 */
	public function addFeatures($features) {
		if (is_array ( $features )) {
			for($i = 0; $i < count ( $features ); ++ $i) {
				if (! in_array ( $features [$i], $this->features )) {
					$this->features [] = $features [$i];
				}
			}
		} elseif (! in_array ( $features, $this->features )) {
			$this->features [] = $features;
		}
	}

	/**
	 * Remove a feature
	 *
	 * @param string $feature
	 */
	public function removeFeature($feature) {
		for($i = 0; $i < count ( $this->features ); ++ $i) {
			if ($this->features [$i] == $feature) {
				unset ( $this->features [$i] );
				break;
			}
		}
	}

}
abstract class Session {
	
	/**
	 * Start flag start the cookie regardless of existing cookie
	 *
	 * @var int
	 */
	const START_NOCOOKIE = 1;
	
	/**
	 * Start flag start the session only if the cookie is available
	 *
	 * @var int
	 */
	const START_IFCOOKIE = 2;

	/**
	 * Get the session interface
	 *
	 * @return SessionInstance
	 */
	public static function instance() {
		return Application::instance ()->getSession ();
	}

	/**
	 * Return true if there is a session cookie and its not empty
	 *
	 * @param START_IFCOOKIE|START_NOCOOKIE $flag
	 * @return boolean
	 */
	public static function start($flag) {
		$session = self::instance ();
		if (! $session->isStarted ()) {
			switch ($flag) {
				case self::START_IFCOOKIE :
					$sid = $session->getSessionCookie ()->getCookie ();
					if (! empty ( $sid )) {
						return $session->start ();
					}
					return false;
				
				case self::START_NOCOOKIE :
					return $session->start ();
			}
		}
		return false;
	}

	/**
	 * Return true if the session has been started
	 *
	 * @return boolean
	 */
	public static function isStarted() {
		return self::instance ()->isStarted ();
	}

	/**
	 * Return the instances unique session Id
	 *
	 * @return string
	 */
	public static function getSessionId() {
		return self::instance ()->getSessionId ();
	}

	/**
	 * Set and validate authentication creditials
	 *
	 * @param SessionCredentials $credentials
	 */
	public static function updateCredentials(SessionCredentials $credentials) {
		$session = self::instance ();

		// Puts all the credentials on the session data
		$params = $credentials->getData ();
		foreach ( $params as $name => $value ) {
			$session->set ( $name, $value );
		}
		
		$session->executeCredentialHandlers ( $credentials );
		$session->setCredentials ( $credentials );
	}

	/**
	 * Get the current authenticated session credentials
	 *
	 * @return SessionCredentials
	 */
	public static function getCredentials() {
		return self::instance ()->getCredentials ();
	}

	/**
	 * Destroys the current session
	 *
	 * @return void
	 */
	public static function destroy() {
		$session = self::instance ();
		$session->executeCleanupHandlers ();
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
		$session = self::instance ();
		return self::instance ()->set ( $name, $value );
	}

	/**
	 * Check if the creditials has a specific role
	 *
	 * @param int $roleId
	 * @return boolean
	 */
	public static function hasRole($roleId) {
		$credentials = self::getCredentials ();
		if (! empty ( $credentials ) && $credentials->hasRole ( $roleId )) {
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
		$credentials = self::getCredentials ();
		if (! empty ( $credentials ) && $credentials->hasFeature ( $featureId )) {
			return true;
		}
		return false;
	}

}