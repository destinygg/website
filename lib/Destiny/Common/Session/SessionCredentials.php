<?php
namespace Destiny\Common\Session;

use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\FilterParams;
use JsonSerializable;

class SessionCredentials implements JsonSerializable {

    protected $userId = null;
    protected $authProvider = '';
    protected $username = '';
    protected $userStatus = '';
    protected $country = '';
    protected $createdDate;
    protected $roles = [];
    protected $features = [];

    /**
     * @var array
     */
    protected $subscription;

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
                $this->setUserId($params ['userId']);
            }
            if (!FilterParams::isEmpty($params, 'username')) {
                $this->setUsername($params ['username']);
            }
            if (!FilterParams::isEmpty($params, 'country')) {
                $this->setCountry($params ['country']);
            }
            if (!FilterParams::isEmpty($params, 'authProvider')) {
                $this->setAuthProvider($params ['authProvider']);
            }
            if (!FilterParams::isEmpty($params, 'userStatus')) {
                $this->setUserStatus($params ['userStatus']);
            }
            if (!FilterParams::isEmpty($params, 'createdDate')) {
                $this->setCreatedDate(Date::getDateTime($params ['createdDate'])->format(Date::FORMAT));
            }
            if (FilterParams::isArray($params, 'features')) {
                $this->setFeatures(array_unique($params ['features']));
            }
            if (FilterParams::isArray($params, 'roles')) {
                $this->setRoles(array_unique($params ['roles']));
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
            'nick' => $this->getUsername(),
            'username' => $this->getUsername(),
            'userId' => $this->getUserId(),
            'status' => $this->getUserStatus(),
            'createdDate' => $this->getCreatedDate(),
            'roles' => $this->getRoles(),
            'features' => $this->getFeatures(),
            'subscription' => $this->getSubscription()
        ];
    }

    /**
     * @return array
     */
    public function getData() {
        return [
            'nick' => $this->getUsername(),
            'username' => $this->getUsername(),
            'userId' => $this->getUserId(),
            'userStatus' => $this->getUserStatus(),
            'createdDate' => $this->getCreatedDate(),
            'country' => $this->getCountry(),
            'roles' => $this->getRoles(),
            'authProvider' => $this->getAuthProvider(),
            'features' => $this->getFeatures(),
            'subscription' => $this->getSubscription()
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

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles(array $roles) {
        $this->roles = $roles;
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
     * @param string $role
     */
    public function removeRole($role) {
        for ($i = 0; $i < count($this->roles); ++$i) {
            if ($this->roles [$i] == $role) {
                unset ($this->roles [$i]);
                break;
            }
        }
    }

    /**
     * @param string $roleId
     * @return bool
     */
    public function hasRole($roleId) {
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

    public function getCreatedDate() {
        return $this->createdDate;
    }

    public function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;
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

    /**
     * Remove a feature
     *
     * @param string $feature
     */
    public function removeFeature($feature) {
        for ($i = 0; $i < count($this->features); ++$i) {
            if ($this->features [$i] == $feature) {
                unset ($this->features [$i]);
                break;
            }
        }
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
}