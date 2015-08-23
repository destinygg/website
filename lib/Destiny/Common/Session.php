<?php 
namespace Destiny\Common;

abstract class Session {

    /**
     * Get the session interface
     *
     * @return SessionInstance
     */
    public static function instance() {
        return Application::instance ()->getSession ();
    }

    /**
     * Returns true if the session cookie isset and has value
     */
    public static function hasSessionCookie() {
        $session = self::instance ();
        $sid = $session->getSessionCookie ()->getValue ();
        return !empty($sid);
    }

    /**
     * Start the session if not already started
     *
     * @return boolean
     */
    public static function start() {
        $session = self::instance ();
        return (!$session->isStarted()) ? $session->start () : true;
    }

    /**
     * Return true if the session has been started
     *
     * @return boolean
     */
    public static function isStarted() {
        return self::instance ()->isStarted ();
    }

    /**
     * Return the instances unique session Id
     *
     * @return string
     */
    public static function getSessionId() {
        return self::instance ()->getSessionId ();
    }

    /**
     * Updates the session variables
     *
     * @param SessionCredentials $credentials
     */
    public static function updateCredentials(SessionCredentials $credentials) {
        $session = self::instance ();
        $params = $credentials->getData ();
        foreach ( $params as $name => $value ) {
            $session->set ( $name, $value );
        }
        $session->setCredentials ( $credentials );
    }

    /**
     * Get the current authenticated session credentials
     *
     * @return SessionCredentials
     */
    public static function getCredentials() {
        return self::instance ()->getCredentials ();
    }

    /**
     * Destroys the current session
     *
     * @return void
     */
    public static function destroy() {
        $session = self::instance ();
        $session->destroy ();
    }

    /**
     * Get a session variable
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name) {
        return self::instance ()->get ( $name );
    }

    /**
     * Set a session variable, if the value is NULL, unset the variable
     * Returns the variable like a getter, but does the setter stuff too
     *
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public static function set($name, $value = null) {
        $session = self::instance ();
        return self::instance ()->set ( $name, $value );
    }

    /**
     * Check if the creditials has a specific role
     *
     * @param int $roleId
     * @return boolean
     */
    public static function hasRole($roleId) {
        $credentials = self::getCredentials ();
        if (! empty ( $credentials ) && $credentials->hasRole ( $roleId )) {
            return true;
        }
        return false;
    }

    /**
     * Check if the creditials has a specific feature
     *
     * @param int $featureId
     * @return boolean
     */
    public static function hasFeature($featureId) {
        $credentials = self::getCredentials ();
        if (! empty ( $credentials ) && $credentials->hasFeature ( $featureId )) {
            return true;
        }
        return false;
    }

}
?>