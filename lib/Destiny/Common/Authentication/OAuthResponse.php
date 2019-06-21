<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Utils\Options;

class OAuthResponse {

    public $authProvider = '';
    public $authId = 0;
    public $authDetail = '';
    public $authEmail = '';
    public $accessToken = '';
    public $refreshToken = '';
    public $username = '';
    public $discriminator = '';
    public $verified = true;

    public function __construct(array $options = null) {
        Options::setOptions($this, $options);
    }

    function __sleep() {
        return [
            'username',
            'discriminator',
            'authId',
            'authProvider',
            'authDetail',
            'authEmail',
            'accessToken',
            'refreshToken',
            'verified'
        ];
    }

    public function isValid(): bool {
        return !(empty($this->authId) || empty($this->accessToken) || empty ($this->authProvider));
    }

    public function getAuthProvider(): string {
        return $this->authProvider;
    }

    public function getAuthId(): string {
        return $this->authId;
    }

    public function getAuthDetail(): string {
        return $this->authDetail;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getDiscriminator(): string {
        return $this->discriminator;
    }

    public function getAuthEmail(): string {
        return $this->authEmail;
    }

    public function getRefreshToken(): string {
        return $this->refreshToken;
    }

    public function getAccessToken(): string {
        return $this->accessToken;
    }

    public function getVerified(): bool {
        return $this->verified;
    }

}