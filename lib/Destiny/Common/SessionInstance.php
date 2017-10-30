<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Options;

class SessionInstance {
    
    /**
     * @var boolean
     */
    protected $started = false;
    
    /**
     * @var string
     */
    protected $sessionId = '';
    
    /**
     * @var Cookie
     */
    protected $sessionCookie = null;
    
    /**
     * @var Cookie
     */
    protected $rememberMeCookie = null;
    
    /**
     * @var SessionCredentials
     */
    protected $credentials = null;

    /**
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

    public function getCredentials() {
        return $this->credentials;
    }

    public function setCredentials(SessionCredentials $credentials) {
        $this->credentials = $credentials;
    }

    public function getSessionCookie() {
        return $this->sessionCookie;
    }

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

    public function getRememberMeCookie() {
        return $this->rememberMeCookie;
    }

    public function setRememberMeCookie(Cookie $sessionCookie) {
        $this->rememberMeCookie = $sessionCookie;
    }

    public function getSessionId() {
        return $this->sessionId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

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
     * @return mixed
     */
    public function get($name) {
        return (isset ( $_SESSION [$name] )) ? $_SESSION [$name] : null;
    }

    /**
     * Set a variable by name
     *
     * @param string $name
     * @param mixed $value
     * @return mixed|null
     */
    public function set($name, $value) {
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
     * Return TRUE if property exists else FALSE
     *
     * @param string $name
     * @return boolean
     */
    public function has($name) {
        return isset ( $_SESSION [$name] );
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