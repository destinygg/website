<?php
namespace Destiny\Common\Session;

use Destiny\Common\Application;
use Destiny\Common\ViewModel;

abstract class Session {

    const KEY_AUTH_SESSION = 'authSession';

    /**
     * @return SessionInstance
     */
    public static function instance() {
        return Application::instance()->getSession();
    }

    /**
     * @return boolean
     */
    public static function hasSessionCookie() {
        $session = self::instance();
        $sid = $session->getSessionCookie()->getValue();
        return !empty($sid);
    }

    /**
     * Start the session if not already started
     *
     * @return boolean
     */
    public static function start() {
        $session = self::instance();
        return (!$session->isStarted()) ? $session->start() : true;
    }

    /**
     * @return boolean
     */
    public static function isStarted() {
        return self::instance()->isStarted();
    }

    /**
     * @return string
     */
    public static function getSessionId() {
        return self::instance()->getSessionId();
    }

    /**
     * @param SessionCredentials $credentials
     */
    public static function updateCredentials(SessionCredentials $credentials) {
        $session = self::instance();
        $params = $credentials->getData();
        foreach ($params as $name => $value) {
            $session->set($name, $value);
        }
        $session->setCredentials($credentials);
    }

    /**
     * @return SessionCredentials
     */
    public static function getCredentials() {
        return self::instance()->getCredentials();
    }

    /**
     * @return void
     */
    public static function destroy() {
        $session = self::instance();
        $session->destroy();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function get($name) {
        return self::instance()->get($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getAndRemove($name) {
        $v = self::instance()->get($name);
        self::instance()->set($name, null);
        return $v;
    }

    /**
     * Set a session variable, if the value is NULL, unset the variable
     * Returns the variable like a getter, but does the setter stuff too
     *
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public static function set($name, $value) {
        return self::instance()->set($name, $value);
    }

    /**
     * Remove a session variable if it exists
     *
     * @param string $name
     */
    public static function remove($name) {
        self::instance()->set($name, null);
    }

    /**
     * @param string $name
     * @return boolean
     */
    public static function has($name) {
        return self::instance()->has($name);
    }

    /**
     * Check if the credential's has a specific role
     *
     * @param string $roleId
     * @return boolean
     */
    public static function hasRole($roleId) {
        $credentials = self::getCredentials();
        if (!empty ($credentials) && $credentials->hasRole($roleId)) {
            return true;
        }
        return false;
    }

    /**
     * Check if the credential's has a specific feature
     *
     * @param string $featureId
     * @return boolean
     */
    public static function hasFeature($featureId) {
        $credentials = self::getCredentials();
        if (!empty ($credentials) && $credentials->hasFeature($featureId)) {
            return true;
        }
        return false;
    }

    public static function applyBags(ViewModel $model) {
        if (self::has('modelSuccess')) {
            $model->success = self::get('modelSuccess');
            self::remove('modelSuccess');
        }
        if (self::has('modelError')) {
            $model->error = self::get('modelError');
            self::remove('modelError');
        }
    }

    public static function setSuccessBag($message) {
        self::set('modelSuccess', $message);
    }

    public static function setErrorBag($message) {
        self::set('modelError', $message);
    }

}