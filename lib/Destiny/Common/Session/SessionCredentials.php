<?php
namespace Destiny\Common\Session;

use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use JsonSerializable;

class SessionCredentials implements JsonSerializable {

    /**
     * @var int|null
     */
    public $userId = null;
    public $authProvider = '';
    public $username = '';
    public $userStatus = '';
    public $country = '';
    public $createdDate;
    public $roles = [];
    public $features = [];

    /**
     * @var array
     */
    public $subscription;

    /**
     * @param array $params
     */
    public function __construct(array $params = null) {
        if (!empty ($params)) {
            $this->setData($params);
        }
    }

    /**
     * @param array $params
     */
    public function setData(array $params) {
        if (!empty ($params)) {
            if (!FilterParams::isEmpty($params, 'userId')) {
                $this->userId = $params['userId'];
            }
            if (!FilterParams::isEmpty($params, 'authProvider')) {
                $this->authProvider = $params['authProvider'];
            }
            if (!FilterParams::isEmpty($params, 'username')) {
                $this->username = $params['username'];
            }
            if (!FilterParams::isEmpty($params, 'userStatus')) {
                $this->userStatus = $params['userStatus'];
            }
            if (!FilterParams::isEmpty($params, 'country')) {
                $this->country = $params['country'];
            }
            if (!FilterParams::isEmpty($params, 'createdDate')) {
                $this->createdDate = Date::getDateTime($params['createdDate'])->format(Date::FORMAT);
            }
            if (FilterParams::isArray($params, 'roles')) {
                $this->roles = array_unique($params['roles']);
            }
            if (FilterParams::isArray($params, 'features')) {
                $this->features = array_unique($params['features']);
            }
        }
    }

    /**
     * I use this to strip out sensitive information when using this object
     * as an API response
     *
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            'nick' => $this->username,
            'username' => $this->username,
            'userId' => $this->userId,
            'status' => $this->userStatus,
            'createdDate' => $this->createdDate,
            'roles' => $this->roles,
            'features' => $this->features,
            'subscription' => $this->subscription
        ];
    }

    /**
     * @return array
     */
    public function getData() {
        return [
            'nick' => $this->username,
            'username' => $this->username,
            'userId' => $this->userId,
            'userStatus' => $this->userStatus,
            'createdDate' => $this->createdDate,
            'country' => $this->country,
            'roles' => $this->roles,
            'authProvider' => $this->authProvider,
            'features' => $this->features,
            'subscription' => $this->subscription
        ];
    }

    /**
     * Checks whether or not the credentials are populated and valid
     * username, userId and userStatus must be set and not empty
     *
     * @return boolean
     */
    public function isValid() {
        $data = $this->getData();
        if (empty ($data ['userId']) && intval($data ['userId']) > 0) {
            return false;
        }
        if (empty ($data ['username'])) {
            return false;
        }
        if (empty ($data ['userStatus'])) {
            return false;
        }
        return true;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRoles() {
        return $this->roles;
    }

    /**
     * @param string[]|string $role
     */
    public function addRoles($role) {
        if (is_array($role)) {
            for ($i = 0; $i < count($role); ++$i) {
                if (!in_array($role [$i], $this->roles)) {
                    $this->roles [] = $role [$i];
                }
            }
        } elseif (!in_array($role, $this->roles)) {
            $this->roles [] = $role;
        }
    }

    /**
     * @param string $roleId
     * @return bool
     */
    public function hasRole($roleId): bool {
        foreach ($this->roles as $role) {
            if (strcasecmp($role, $roleId) === 0) {
                return true;
            }
        }
        return false;
    }

    public function getCountry() {
        return $this->country;
    }

    public function getUserId() {
        return $this->userId;
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

    public function getFeatures() {
        return $this->features;
    }

    public function getCreatedDate() {
        return $this->createdDate;
    }

    /**
     * @return array
     */
    public function getSubscription() {
        return $this->subscription;
    }

    /**
     * @param array $subscription
     */
    public function setSubscription($subscription) {
        $this->subscription = $subscription;
    }

    /**
     * @param string $featureName
     * @return bool
     */
    public function hasFeature($featureName) {
        foreach ($this->features as $feature) {
            if (strcasecmp($feature, $featureName) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add user features
     *
     * @param string[]|string $features
     */
    public function addFeatures($features) {
        if (is_array($features)) {
            for ($i = 0; $i < count($features); ++$i) {
                if (!in_array($features [$i], $this->features)) {
                    $this->features [] = $features [$i];
                }
            }
        } elseif (!in_array($features, $this->features)) {
            $this->features [] = $features;
        }
    }
}