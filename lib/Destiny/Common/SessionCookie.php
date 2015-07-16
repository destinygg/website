<?php 
namespace Destiny\Common;

use Destiny\Common\Utils\Options;
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

    protected $secure = false;
    protected $httponly = true;

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

    public function getSecure() {
        return $this->secure;
    }

    public function getHttpOnly() {
        return $this->httponly;
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

    public function setSecure($secure) {
        $this->secure = $secure;
    }

    public function setHttpOnly($httponly) {
        $this->httponly = $httponly;
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
?>