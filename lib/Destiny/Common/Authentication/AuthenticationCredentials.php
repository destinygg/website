<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Utils\Options;

class AuthenticationCredentials {

    private $authProvider;
    private $authCode;
    private $authId;
    private $authDetail;
    private $refreshToken;
    private $username;
    private $email;

    public function __construct(array $options = null) {
        Options::setOptions ( $this, $options );
    }

    function __sleep() {
        return [
            'authProvider',
            'authCode',
            'authId',
            'authDetail',
            'refreshToken',
            'username',
            'email'
        ];
    }

    public function isValid() {
        if (empty ( $this->authId ) || empty ( $this->authCode ) || empty ( $this->authProvider )) {
            return false;
        }
        return true;
    }

    public function getAuthProvider() {
        return $this->authProvider;
    }

    public function setAuthProvider($authProvider) {
        $this->authProvider = $authProvider;
    }

    public function getAuthCode() {
        return $this->authCode;
    }

    public function setAuthCode($authCode) {
        $this->authCode = $authCode;
    }

    public function getAuthId() {
        return $this->authId;
    }

    public function setAuthId($authId) {
        $this->authId = $authId;
    }

    public function getAuthDetail() {
        return $this->authDetail;
    }

    public function setAuthDetail($authDetail) {
        $this->authDetail = $authDetail;
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

    public function getRefreshToken() {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken) {
        $this->refreshToken = $refreshToken;
    }

}