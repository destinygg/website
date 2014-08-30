<?php 
namespace Destiny\Common;

use Destiny\Chat\ChatIntegrationService;

abstract class Session {

    /**
     * Start flag start the cookie regardless of existing cookie
     *
     * @var int
     */
    const START_NOCOOKIE = 1;

    /**
     * Start flag start the session only if the cookie is available
     *
     * @var int
     */
    const START_IFCOOKIE = 2;

    /**
     * Get the session interface
     *
     * @return SessionInstance
     */
    public static function instance() {
        return Application::instance ()->getSession ();
    }

    /**
     * Return true if there is a session cookie and its not empty
     *
     * @param START_IFCOOKIE|START_NOCOOKIE $flag
     * @return boolean
     */
    public static function start($flag) {
        $session = self::instance ();
        if (! $session->isStarted ()) {
            switch ($flag) {
                case self::START_IFCOOKIE :
                    $sid = $session->getSessionCookie ()->getCookie ();
                    if (! empty ( $sid )) {
                        ChatIntegrationService::instance ()->renewChatSessionExpiration ( $sid );
                        return $session->start ();
                    }
                    return false;

                case self::START_NOCOOKIE :
                    return $session->start ();
            }
        }
        return false;
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
     * @return mix
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
     * @return mix
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