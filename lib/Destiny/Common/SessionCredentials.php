<?php
namespace Destiny\Common;

use Destiny\Common\Utils\Date;

class SessionCredentials {

    protected $userId = null;
    protected $authProvider = '';
    protected $username = '';
    protected $userStatus = '';
    protected $email = '';
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
            if (isset ($params ['userId']) && !empty ($params ['userId'])) {
                $this->setUserId($params ['userId']);
            }
            if (isset ($params ['username']) && !empty ($params ['username'])) {
                $this->setUsername($params ['username']);
            }
            if (isset ($params ['email']) && !empty ($params ['email'])) {
                $this->setEmail($params ['email']);
            }
            if (isset ($params ['country']) && !empty ($params ['country'])) {
                $this->setCountry($params ['country']);
            }
            if (isset ($params ['authProvider']) && !empty ($params ['authProvider'])) {
                $this->setAuthProvider($params ['authProvider']);
            }
            if (isset ($params ['userStatus']) && !empty ($params ['userStatus'])) {
                $this->setUserStatus($params ['userStatus']);
            }
            if (isset ($params ['createdDate']) && !empty ($params ['createdDate'])) {
                $this->setCreatedDate(Date::getDateTime($params ['createdDate'])->format(Date::FORMAT));
            }
            if (isset ($params ['features']) && !empty ($params ['features']) && is_array($params ['features'])) {
                $this->setFeatures(array_unique($params ['features']));
            }
            if (isset ($params ['roles']) && !empty ($params ['roles']) && is_array($params ['roles'])) {
                $this->setRoles(array_unique($params ['roles']));
            }
        }
    }

    /**
     * @return array
     */
    public function getData() {
        return [
            'email' => $this->getEmail(),
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

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
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