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
     * @var Cookie
     */
    protected $sessionCookie = null;
    
    /**
     * The remember me cookie
     *
     * @var Cookie
     */
    protected $rememberMeCookie = null;
    
    /**
     * The session authentication
     *
     * @var SessionCredentials
     */
    protected $credentials = null;

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
        $this->getSessionCookie ()->clearCookie ();
        $this->getRememberMeCookie ()->clearCookie ();
        if ($this->isStarted ()) {
            session_destroy ();
            $_SESSION = array ();
            session_regenerate_id ( false );
        }
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
     * @param \Destiny\Cookie $credentials
     */
    public function setCredentials(SessionCredentials $credentials) {
        $this->credentials = $credentials;
    }

    /**
     * Get the session cookie
     *
     * @return \Destiny\Cookie
     */
    public function getSessionCookie() {
        return $this->sessionCookie;
    }

    /**
     * Set the session cookie
     *
     * @param \Destiny\Cookie $sessionCookie
     */
    public function setSessionCookie(Cookie $sessionCookie) {
        $this->sessionCookie = $sessionCookie;
        session_set_cookie_params (
            $sessionCookie->getLife (),
            $sessionCookie->getPath (),
            $sessionCookie->getDomain (),
            $sessionCookie->getSecure (),
            $sessionCookie->getHttpOnly ()
        );
        session_name ( $sessionCookie->getName () );
    }

    /**
     * Get the remember me cookie
     *
     * @return \Destiny\Cookie
     */
    public function getRememberMeCookie() {
        return $this->rememberMeCookie;
    }

    /**
     * Set the remember me cookie
     *
     * @param \Destiny\Cookie $sessionCookie
     */
    public function setRememberMeCookie(Cookie $sessionCookie) {
        $this->rememberMeCookie = $sessionCookie;
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

}