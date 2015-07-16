<?php 
namespace Destiny\Common;

use Destiny\Common\Utils\Options;

class Cookie {

    protected $name = '';
    protected $life = 0;
    protected $path = '/';
    protected $domain = '';
    protected $secure = false;
    protected $httponly = true;

    public function __construct($name, array $params = null) {
        $this->setName ( $name );
        if (! empty ( $params )) {
            Options::setOptions ( $this, $params );
        }
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getLife() {
        return $this->life;
    }

    public function setLife($life) {
        $this->life = $life;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getSecure() {
        return $this->secure;
    }

    public function setSecure($secure) {
        $this->secure = $secure;
    }

    public function getHttpOnly() {
        return $this->httponly;
    }

    public function setHttpOnly($httponly) {
        $this->httponly = $httponly;
    }

    public function getValue() {
        if (isset ( $_COOKIE [$this->name] )) {
            return $_COOKIE [$this->name];
        }
        return null;
    }

    public function setValue($value, $expiry) {
        $_COOKIE [$this->name] = $value;
        setcookie ( $this->name, $value, $expiry, $this->getPath (), $this->getDomain (), $this->getSecure(), $this->getHttpOnly() );
    }
    
    public function clearCookie() {
        if (isset ( $_COOKIE [$this->name] )) {
            unset ( $_COOKIE [$this->name] );
        }
        setcookie ( $this->name, '', time () - 3600, $this->getPath (), $this->getDomain (), $this->getSecure(), $this->getHttpOnly() );
    }

}
?>