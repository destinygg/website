<?php
namespace Destiny\Common\Session;

use Destiny\Common\Application;
use Destiny\Common\ViewModel;

abstract class Session {

    /**
     * @return SessionInstance|null
     */
    public static function instance() {
        return Application::instance()->getSession();
    }

    public static function start(): bool {
        $session = self::instance();
        return (!$session->isStarted()) ? $session->start() : true;
    }

    public static function isStarted(): bool {
        return self::instance()->isStarted();
    }

    public static function getSessionId(): string {
        return self::instance()->getSessionId();
    }

    /**
     * @return SessionCredentials|null
     */
    public static function getCredentials() {
        return self::instance()->getCredentials();
    }

    public static function get(string $name) {
        return self::instance()->get($name);
    }

    public static function getAndRemove(string $name) {
        $v = self::instance()->get($name);
        self::instance()->set($name, null);
        return $v;
    }

    /**
     * Set a session variable, if the value is NULL, unset the variable
     */
    public static function set(string $name, $value = null) {
        self::instance()->set($name, $value);
    }

    public static function remove(string $name) {
        self::instance()->set($name, null);
    }

    public static function has(string $name): bool {
        return self::instance()->has($name);
    }

    public static function hasRole(string $roleId): bool {
        $credentials = self::getCredentials();
        return (!empty ($credentials) && $credentials->hasRole($roleId));
    }

    public static function hasFeature(string $featureId): bool {
        $credentials = self::getCredentials();
        return (!empty ($credentials) && $credentials->hasFeature($featureId));
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

    public static function setSuccessBag(string $message) {
        self::set('modelSuccess', $message);
    }

    public static function setErrorBag(string $message) {
        self::set('modelError', $message);
    }

}