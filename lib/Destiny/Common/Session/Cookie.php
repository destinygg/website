<?php
namespace Destiny\Common\Session;

use Destiny\Common\Utils\Options;

class Cookie {

    public $name = '';
    public $life = 0;
    public $path = '/';
    public $domain = '';
    public $secure = false;
    public $httponly = true;
    public $samesite = 'lax';

    public function __construct($name, array $params = null) {
        $this->setName($name);
        if (!empty ($params)) {
            Options::setOptions($this, $params);
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getLife(): int {
        return $this->life;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getDomain(): string {
        return $this->domain;
    }

    public function getSecure(): bool {
        return $this->secure;
    }

    public function getHttpOnly(): bool {
        return $this->httponly;
    }

    public function getSameSite(): string {
        return $this->samesite;
    }

    public function getValue() {
        if (isset ($_COOKIE[$this->name])) {
            return $_COOKIE[$this->name];
        }
        return null;
    }

    public function setValue($value, $expiry) {
        $_COOKIE[$this->name] = $value;
        setcookie(
            $this->name,
            $value,
            [
                'expires' => $expiry,
                'path' => $this->getPath(),
                'domain' => $this->getDomain(),
                'secure' => $this->getSecure(),
                'httponly' => $this->getHttpOnly(),
                'samesite' => $this->getSameSite()
            ]
        );
    }

    public function clearCookie() {
        if (isset ($_COOKIE[$this->name])) {
            unset ($_COOKIE[$this->name]);
        }
        setcookie(
            $this->name,
            '',
            [
                'expires' => time() - 3600,
                'path' => $this->getPath(),
                'domain' => $this->getDomain(),
                'secure' => $this->getSecure(),
                'httponly' => $this->getHttpOnly(),
                'samesite' => $this->getSameSite()
            ]
        );
    }

}