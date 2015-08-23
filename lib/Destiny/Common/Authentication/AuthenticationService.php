<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Config;
use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Service;
use Destiny\Common\SessionCredentials;
use Destiny\Common\User\UserRole;
use Destiny\Common\User\UserFeature;
use Destiny\Common\User\UserService;
use Destiny\Common\User\UserFeaturesService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Chat\ChatIntegrationService;

/**
 * @method static AuthenticationService instance()
 */
class AuthenticationService extends Service {
    
    /**
     * @param string $username
     * @param array $user
     * @throws Exception
     */
    public function validateUsername($username, array $user = null) {
        if (empty ( $username ))
            throw new Exception ( 'Username required' );

        if (preg_match ( '/^[A-Za-z0-9_]{3,20}$/', $username ) == 0)
            throw new Exception ( 'Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.' );

        // nick-to-emote similarity heuristics, not perfect sadly ;(
        $normalizeduname = strtolower( $username );
        $front = substr( $normalizeduname, 0, 2 );
        foreach( Config::$a ['chat'] ['customemotes'] as $emote ) {
            $normalizedemote = strtolower( $emote );
            if ( strpos( $normalizeduname, $normalizedemote ) === 0 )
                throw new Exception ( 'Username too similar to an emote, try changing the first characters' );

            if ( $emote == 'LUL' )
                continue;

            $shortuname = substr( $normalizeduname, 0, strlen( $emote ) );
            $emotefront = substr( $normalizedemote, 0, 2 );
            if ( $front == $emotefront and levenshtein( $normalizedemote, $shortuname ) <= 2 )
                throw new Exception ( 'Username too similar to an emote, try changing the first characters' );
        }

        if (preg_match_all ( '/[0-9]{3}/', $username, $m ) > 0)
            throw new Exception ( 'Too many numbers in a row' );
        
        if (preg_match_all ( '/[\_]{2}/', $username, $m ) > 0 || preg_match_all ( "/[_]+/", $username, $m ) > 2)
            throw new Exception ( 'Too many underscores' );
        
        if (preg_match_all ( "/[0-9]/", $username, $m ) > round ( strlen ( $username ) / 2 ))
            throw new Exception ( 'Number ratio is too damn high' );
        
        if (UserService::instance ()->getIsUsernameTaken ( $username, ((! empty ( $user )) ? $user ['userId'] : 0) ))
            throw new Exception ( 'The username you asked for is already being used' );
    }

    /**
     * @param string $email
     * @param array $user
     * @param null|boolean $skipusercheck
     * @throws Exception
     */
    public function validateEmail($email, array $user = null, $skipusercheck = null) {
        if (! filter_var ( $email, FILTER_VALIDATE_EMAIL ))
            throw new Exception ( 'A valid email is required' );
        
        if (! $skipusercheck and ! empty ( $user )) {
            if (UserService::instance ()->getIsEmailTaken ( $email, $user ['userId'] ))
                throw new Exception ( 'The email you asked for is already being used' );
        } elseif (! $skipusercheck ) {
            if (UserService::instance ()->getIsEmailTaken ( $email ))
                throw new Exception ( 'The email you asked for is already being used' );
        }

        $emailDomain = strtolower( substr( $email, strpos( $email, '@' ) + 1 ) );
        if ( isset( Config::$a ['blacklistedDomains'][ $emailDomain ] ) )
            throw new Exception ( 'The email is blacklisted' );

    }

    /**
     * Starts up the session, looks for remember me if there was no session
     * Also updates the session if the user is flagged for it.
     *
     * @throws Exception
     */
    public function startSession() {

        // If the session has a cookie, start it
        if ( Session::hasSessionCookie () && Session::start() && Session::hasRole ( UserRole::USER ) ) {
            ChatIntegrationService::instance ()->renewChatSessionExpiration ( Session::getSessionId () );
        }

        // Check the Remember me cookie if the session is invalid
        if( !Session::hasRole ( UserRole::USER ) ){
            $rememberMe = $this->getRememberMe ();
            if (!empty($rememberMe) && isset($rememberMe['userId']) && !empty ( $rememberMe['userId'] )) {
                $user = UserService::instance ()->getUserById ( $rememberMe['userId'] );
                if (! empty ( $user )) {

                    Session::start();
                    Session::updateCredentials ( $this->getUserCredentials ( $user, 'rememberme' ) );

                    // This writes to the DB a bit more than it needs to, low impact, leaving here.
                    $this->setRememberMe ( $user );

                    // flagUserForUpdate updates the credentials AGAIN, but since its low impact
                    // Instead of doing the logic in two places 
                    $this->flagUserForUpdate ( $user['userId'] );
                }
            }
        }

        // Update the user if they have been flagged for an update
        if( Session::hasRole ( UserRole::USER ) ) {
            $userId = Session::getCredentials ()->getUserId ();
            if( !empty($userId) && $this->isUserFlaggedForUpdate ( $userId ) ){
                $user = UserService::instance ()->getUserById ( $userId );
                if ( !empty ( $user ) ) {
                    $this->clearUserUpdateFlag ( $userId );
                    Session::updateCredentials ( $this->getUserCredentials ( $user, 'session' ) );
                    // the refreshChatSession differs from this call, because only here we have access to the session id.
                    ChatIntegrationService::instance ()->setChatSession ( Session::getCredentials(), Session::getSessionId () );
                }
            }
        }
    }

    /**
     * Create a credentials object for a specific user
     *
     * @param array $user
     * @param string $authProvider
     * @return SessionCredentials
     */
    public function getUserCredentials(array $user, $authProvider) {
        $credentials = new SessionCredentials ( $user );
        $credentials->setAuthProvider ( $authProvider );
        $credentials->addRoles ( UserRole::USER );
        $credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] ) );
        $credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $user ['userId'] ) );

        $subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
        if (! empty ( $subscription ) or $user ['istwitchsubscriber']) {
            $credentials->addRoles ( UserRole::SUBSCRIBER );
            $credentials->addFeatures ( UserFeature::SUBSCRIBER );

            if ( $user['istwitchsubscriber'] )
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT0 );
        }

        if (! empty( $subscription )) {
            if ($subscription ['subscriptionTier'] == 2) {
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT2 );
            }
            if ($subscription ['subscriptionTier'] == 3) {
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT3 );
            }
            if ($subscription ['subscriptionTier'] == 4) {
                $credentials->addFeatures ( UserFeature::SUBSCRIBERT4 );
            }
        }
        return $credentials;
    }

    /**
     * @param AuthenticationCredentials $authCreds
     * @throws Exception
     */
    public function handleAuthCredentials(AuthenticationCredentials $authCreds) {
        $userService = UserService::instance ();
        $user = $userService->getUserByAuthId ( $authCreds->getAuthId (), $authCreds->getAuthProvider () );
        
        if (empty ( $user )) {
            throw new Exception ( 'Invalid auth user' );
        }
        
        // The user has registed before...
        // Update the auth profile for this provider
        $authProfile = $userService->getUserAuthProfile ( $user ['userId'], $authCreds->getAuthProvider () );
        if (! empty ( $authProfile )) {
            $userService->updateUserAuthProfile ( $user ['userId'], $authCreds->getAuthProvider (), array (
                'authCode' => $authCreds->getAuthCode (),
                'authDetail' => $authCreds->getAuthDetail () 
            ) );
        }
        
        // Renew the session upon successful login, makes it slightly harder to hijack
        $session = Session::instance ();
        $session->renew ( true );
        
        $credentials = $this->getUserCredentials ( $user, $authCreds->getAuthProvider () );
        Session::updateCredentials ( $credentials );
        ChatIntegrationService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
        
        // Variable is sent from the login form
        if (Session::set ( 'rememberme' )) {
            $this->setRememberMe ( $user );
        }
    }

    /**
     * Handles the authentication and then merging of accounts
     * Merging of an account is basically connecting multiple authenticators to one user
     *
     * @param AuthenticationCredentials $authCreds
     * @throws Exception
     */
    public function handleAuthAndMerge(AuthenticationCredentials $authCreds) {
        $userService = UserService::instance ();
        $user = $userService->getUserByAuthId ( $authCreds->getAuthId (), $authCreds->getAuthProvider () );
        $sessAuth = Session::getCredentials ()->getData ();
        // We need to merge the accounts if one exists
        if (! empty ( $user )) {
            // If the profile userId is the same as the current one, the profiles are connceted, they shouldnt be here
            if ($user ['userId'] == $sessAuth ['userId']) {
                throw new Exception ( 'These account are already connected' );
            }
            // If the profile user is older than the current user, prompt the user to rather login using the other profile
            if (intval ( $user ['userId'] ) < $sessAuth ['userId']) {
                throw new Exception ( sprintf ( 'Your user profile for the %s account is older. Please login and use that account to merge.', $authCreds->getAuthProvider () ) );
            }
            // So we have a profile for a different user to the one logged in, we delete that user, and add a profile for the current user
            $userService->removeAuthProfile ( $user ['userId'], $authCreds->getAuthProvider () );
            // Set the user profile to Merged
            $userService->updateUser ( $user ['userId'], array (
                'userStatus' => 'Merged' 
            ) );
        }
        $userService->addUserAuthProfile ( array (
            'userId' => $sessAuth ['userId'],
            'authProvider' => $authCreds->getAuthProvider (),
            'authId' => $authCreds->getAuthId (),
            'authCode' => $authCreds->getAuthCode (),
            'authDetail' => $authCreds->getAuthDetail () 
        ) );
    }

    /**
     * Generates a rememberme record and cookie
     * Note the rememberme cookie has a long expiry unlike the session cookie
     *
     * @param array $user
     * @return null|string
     */
    protected function setRememberMe(array $user) {
        $rememberMeService = RememberMeService::instance ();
        $cookie = Session::instance()->getRememberMeCookie();
        $token = $cookie->getValue();

        // Clean out old token
        if (! empty ( $token )) {
            $rememberMeService->deleteRememberMe ( $user ['userId'], $token, 'rememberme' );
            $cookie->clearCookie();
        }

        // Create the new token and record
        $createdDate = Date::getDateTime ( 'NOW' );
        $expireDate = Date::getDateTime ( 'NOW + 30 day' );
        $token = md5 ( $user ['userId'] . $createdDate->getTimestamp () . $expireDate->getTimestamp () . rand(1000, 9999) );
        $rememberMeService->addRememberMe ( $user ['userId'], $token, 'rememberme', $expireDate, $createdDate );
        $cookie->setValue ( $token, $expireDate->getTimestamp () );
        return $token;
    }

    /**
     * Returns the remember me record for the current cookie
     *
     * @return array
     */
    protected function getRememberMe() {
        $rememberMeService = RememberMeService::instance ();
        $cookie = Session::instance()->getRememberMeCookie();
        $token = $cookie->getValue();
        $rememberMe = null;

        // throw back to when I used a json string in the rememberme cookie
        // this is here so no-ones remember me cookie failed after upgrade.
        if(!empty($token) && $token[0] == "{"){
            $cookieData = @json_decode ( $token, true );
            if(!empty ( $cookieData ) && isset($cookieData ['token'])){
                $token = $cookieData ['token'];
            }
        }

        // If the token is not empty query the DB for the remember me record
        if (! empty ( $token )) {
            $rememberMe = $rememberMeService->getRememberMe ( $token, 'rememberme' );
        }
        return $rememberMe;
    }

    /**
     * Flag a user session for update
     * So that on their next request, the session data is updated.
     * Also does a chat session refresh
     *
     * @param int $userId
     */
    public function flagUserForUpdate($userId) {
        $user = UserService::instance ()->getUserById ( $userId );
        if(!empty($user)){
            $cache = Application::instance ()->getCacheDriver ();
            $cache->save ( sprintf ( 'refreshusersession-%s', $userId ), time (), intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
            ChatIntegrationService::instance ()->refreshChatUserSession ( $this->getUserCredentials ( $user, 'session' ) );
        }
    }

    /**
     * @param int $userId
     */
    protected function clearUserUpdateFlag($userId) {
        $cache = Application::instance ()->getCacheDriver ();
        $cache->delete ( sprintf ( 'refreshusersession-%s', $userId ));
    }

    /**
     * @param int $userId
     * @return bool
     */
    protected function isUserFlaggedForUpdate($userId) {
        $cache = Application::instance ()->getCacheDriver ();
        $lastUpdated = $cache->fetch ( sprintf ( 'refreshusersession-%s', $userId ) );
        return !empty ($lastUpdated);
    }

}