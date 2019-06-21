<?php
namespace Destiny\Common\Session;

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

    public function __construct(array $params = null) {
        if (!empty($params)) {
            Options::setOptions($this, $params);
        }
    }

    /**
     * Start the session, return true if the session was started otherwise false
     * Copy the global session variables to the $credentials
     */
    public function start(): bool {
        $this->started = session_start();
        $this->setSessionId(session_id());
        $credentials = $this->getCredentials();
        $credentials->setData($_SESSION);
        return $this->started;
    }

    /**
     * Regenerates the session id
     */
    public function renew(): bool {
        if ($this->isStarted() || $this->start()) {
            session_regenerate_id(true);
            $this->setSessionId(session_id());
            return true;
        }
        return false;
    }

    /**
     * Deletes the session and recreates the session Id
     */
    public function destroy() {
        $this->getSessionCookie()->clearCookie();
        $this->getRememberMeCookie()->clearCookie();
        if ($this->isStarted()) {
            $_SESSION = [];
            session_regenerate_id(false);
            session_destroy();
        }
    }

    /**
     * @return SessionCredentials|null
     */
    public function getCredentials() {
        return $this->credentials;
    }

    public function setCredentials(SessionCredentials $credentials) {
        $this->credentials = $credentials;
    }

    /**
     * @return Cookie|null
     */
    public function getSessionCookie() {
        return $this->sessionCookie;
    }

    public function setSessionCookie(Cookie $sessionCookie) {
        $this->sessionCookie = $sessionCookie;
        session_set_cookie_params(
            $sessionCookie->getLife(),
            $sessionCookie->getPath(),
            $sessionCookie->getDomain(),
            $sessionCookie->getSecure(),
            $sessionCookie->getHttpOnly()
        );
        session_name($sessionCookie->getName());
    }

    /**
     * @return Cookie|null
     */
    public function getRememberMeCookie() {
        return $this->rememberMeCookie;
    }

    public function setRememberMeCookie(Cookie $sessionCookie) {
        $this->rememberMeCookie = $sessionCookie;
    }

    public function getSessionId(): string {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId) {
        $this->sessionId = $sessionId;
    }

    public function isEmpty(string $name): bool {
        $value = (isset($_SESSION[$name])) ? $_SESSION[$name] : null;
        return empty($value);
    }

    /**
     * @return mixed|null
     */
    public function get(string $name) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * @param mixed $value
     */
    public function set(string $name, $value) {
        if ($value === null) {
            if (isset($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }
        } else {
            $_SESSION[$name] = $value;
        }
    }

    public function has(string $name): bool {
        return isset($_SESSION[$name]);
    }

    public function isStarted(): bool {
        return $this->started;
    }

}