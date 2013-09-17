<?php
namespace Destiny\Common\Service;

use Destiny\Common\Config;
use Destiny\Common\Application;
use Destiny\Common\Security\AuthenticationCredentials;
use Destiny\Common\Service\RememberMeService;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Date;
use Destiny\Common\Session;
use Destiny\Common\Service;
use Destiny\Common\Service\UserService;
use Destiny\Common\Service\SubscriptionsService;
use Destiny\Common\SessionCredentials;
use Destiny\Common\UserRole;
use Destiny\Common\UserFeature;

class AuthenticationService extends Service {
	
	/**
	 * Singleton
	 *
	 * @var AuthenticationService
	 */
	protected static $instance = null;
	
	/**
	 * The name of the remember me cookie
	 *
	 * @var string
	 */
	protected $remembermeId = '';
	
	/**
	 * The salt for the token
	 *
	 * @var string
	 */
	protected $remembermeSalt = 'r3xCdvd_sqe';

	/**
	 * Singleton
	 *
	 * @return AuthenticationService
	 */
	public static function instance() {
		if (static::$instance === null) {
			static::$instance = new static ();
			static::$instance->remembermeId = Config::$a ['rememberme'] ['cookieName'];
		}
		return static::$instance;
	}

	/**
	 * Validates a username
	 *
	 * @param string $username
	 * @param array $user
	 * @throws Exception
	 */
	public function validateUsername($username, array $user = null) {
		if (empty ( $username )) {
			throw new Exception ( 'Username required' );
		}
		
		if (preg_match ( '/\\b(' . join ( '|', Config::$a ['chat'] ['customemotes'] ) . ')\\b/i', preg_quote ( $username ) ) > 0) {
			throw new Exception ( 'That username has been blacklisted' );
		}
		if (preg_match ( '/^[A-Za-z0-9_]{4,20}$/', $username ) == 0) {
			throw new Exception ( 'Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.' );
		}
		if (preg_match_all ( '/[0-9]{4}/', $username, $m ) > 0) {
			throw new Exception ( 'Too many numbers in a row' );
		}
		if (preg_match_all ( '/[\_]{2}/', $username, $m ) > 0 || preg_match_all ( "/[_]+/", $username, $m ) > 2) {
			throw new Exception ( 'Too many underscores' );
		}
		if (preg_match_all ( "/[0-9]/", $username, $m ) > round ( strlen ( $username ) / 2 )) {
			throw new Exception ( 'Number ratio is too damn high' );
		}
		if (UserService::instance ()->getIsUsernameTaken ( $username, ((! empty ( $user )) ? $user ['userId'] : 0) )) {
			throw new Exception ( 'The username you asked for is already being used' );
		}
	}

	/**
	 * Validate email
	 *
	 * @param string $email
	 * @param array $user
	 * @throws Exception
	 */
	public function validateEmail($email, array $user = null) {
		if (! filter_var ( $email, FILTER_VALIDATE_EMAIL )) {
			throw new Exception ( 'A valid email is required' );
		}
		if (! empty ( $user )) {
			if (UserService::instance ()->getIsEmailTaken ( $email, $user ['userId'] )) {
				throw new Exception ( 'The email you asked for is already being used' );
			}
		} else {
			if (UserService::instance ()->getIsEmailTaken ( $email )) {
				throw new Exception ( 'The email you asked for is already being used' );
			}
		}
	}

	/**
	 * Check if a user has been flagged for updates, and refreshes the session credentials
	 * @throws Exception
	 */
	public function init() {
		$app = Application::instance ();
		// Check if the users session has been flagged for update
		if (Session::isStarted ()) {
			$userId = Session::getCredentials ()->getUserId ();
			$lastUpdate = $this->isUserFlaggedForUpdate ( $userId );
			if (! empty ( $userId ) && $lastUpdate !== false) {
				$this->clearUserUpdateFlag ( $userId, $lastUpdate );
				$userManager = UserService::instance ();
				$user = $userManager->getUserById ( $userId );
				if (! empty ( $user )) {
					// Check the user status
					if (strcasecmp ( $user ['userStatus'], 'Active' ) !== 0) {
						throw new Exception ( sprintf ( 'User status not active. Status: %s', $user ['userStatus'] ) );
					}
					$credentials = $this->getUserCredentials ( $user, 'session' );
					Session::updateCredentials ( $credentials );
					ChatIntegrationService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
				}
			}
		}
	}

	/**
	 * Logout a user
	 */
	public function logout() {
		ChatIntegrationService::instance ()->deleteChatSession ();
		$userId = Session::getCredentials ()->getUserId ();
		if (! empty ( $userId )) {
			$this->clearRememberMe ( $userId );
		}
		Session::destroy ();
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
		
		// Add the user features
		$credentials->addFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] ) );
		
		// Get the stored roles
		$credentials->addRoles ( UserService::instance ()->getUserRolesByUserId ( $user ['userId'] ) );
		
		// Get the users active subscriptions
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
		if (! empty ( $subscription )) {
			$credentials->addRoles ( UserRole::SUBSCRIBER );
			$credentials->addFeatures ( UserFeature::SUBSCRIBER );
			if ($subscription ['subscriptionTier'] == 2) {
				$credentials->addFeatures ( UserFeature::SUBSCRIBERT2 );
			}
		}
		return $credentials;
	}

	/**
	 * Check if a auth profile exists for a user
	 *
	 * @param AuthenticationCredentials $authCreds
	 * @return boolean
	 */
	public function getUserAuthProfileExists(AuthenticationCredentials $authCreds) {
		$user = UserService::instance ()->getUserByAuthId ( $authCreds->getAuthId (), $authCreds->getAuthProvider () );
		if (empty ( $user )) {
			return false;
		}
		return true;
	}

	/**
	 * Handles the credentials after authorization
	 *
	 * @param array $authCreds
	 * @throws Exception
	 */
	public function handleAuthCredentials(AuthenticationCredentials $authCreds) {
		$userService = UserService::instance ();
		$user = $userService->getUserByAuthId ( $authCreds->getAuthId (), $authCreds->getAuthProvider () );
		
		// Make sure there is a user
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
		
		// Check the user status
		if (strcasecmp ( $user ['userStatus'], 'Active' ) !== 0) {
			throw new Exception ( sprintf ( 'User status not active. Status: %s', $user ['userStatus'] ) );
		}
		
		// Renew the session upon successful login, makes it slightly harder to hijack
		$session = Session::instance ();
		$session->renew ( true );
		
		$credentials = $this->getUserCredentials ( $user, $authCreds->getAuthProvider () );
		Session::updateCredentials ( $credentials );
		ChatIntegrationService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
		
		// Remember me (this gets and then unsets the var)
		if (Session::set ( 'rememberme' )) {
			$this->setRememberMe ( $user );
		}
		
		Session::set ( 'authSession' );
	}

	/**
	 * Handles the authentication and then merging of accounts
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
	 * Generates a rememberme record
	 *
	 * @param array $user
	 */
	public function setRememberMe(array $user) {
		$this->clearRememberMe ( $user ['userId'] );
		$createdDate = Date::getDateTime ( 'NOW' );
		$expireDate = Date::getDateTime ( 'NOW + 30 day' );
		$token = md5 ( $user ['userId'] . $createdDate->getTimestamp () . $expireDate->getTimestamp () . $this->remembermeSalt );
		$rememberMeService = RememberMeService::instance ();
		$rememberMeService->addRememberMe ( $user ['userId'], $token, 'rememberme', $expireDate, $createdDate );
		$this->setRememberMeCookie ( $token, $createdDate, $expireDate );
		return $token;
	}

	/**
	 * Returns the current userId of the remember me cookie
	 * Also performs validation on the cookie and the record in the Db
	 * Does not touch the DB unless there is a valid remember me cookie
	 *
	 * @return int false
	 */
	public function getRememberMe() {
		$cookie = $this->getRememberMeCookie ();
		if (! empty ( $cookie ) && isset ( $cookie ['created'] ) && isset ( $cookie ['expire'] ) && isset ( $cookie ['token'] )) {
			$rememberMeService = RememberMeService::instance ();
			$rememberMe = $rememberMeService->getRememberMe ( $cookie ['token'], 'rememberme' );
			if (! empty ( $rememberMe )) {
				try {
					if (Date::getDateTime ( $rememberMe ['createdDate'] ) != Date::getDateTime ( $cookie ['created'] )) {
						throw new Exception ( 'Token invalid [createdDate] does not match' );
					}
					if (Date::getDateTime ( $rememberMe ['expireDate'] ) != Date::getDateTime ( $cookie ['expire'] )) {
						throw new Exception ( 'Token invalid [expireDate] does not match' );
					}
					if ($cookie ['token'] != md5 ( $rememberMe ['userId'] . Date::getDateTime ( $rememberMe ['createdDate'] )->getTimestamp () . Date::getDateTime ( $rememberMe ['expireDate'] )->getTimestamp () . $this->remembermeSalt )) {
						throw new Exception ( 'Token invalid [token] does not match' );
					}
				} catch ( Exception $e ) {
					$this->clearRememberMe ( $rememberMe ['userId'] );
					Application::instance ()->getLogger ()->error ( sprintf ( 'Remember-me: %s', $e->getMessage () ) );
					return false;
				}
				return $rememberMe ['userId'];
			}
		}
		return false;
	}

	/**
	 * Clear the local rememberme cookie
	 *
	 * @param int $userId
	 */
	public function clearRememberMe($userId) {
		$cookie = $this->getRememberMeCookie ();
		if (! empty ( $cookie )) {
			$rememberMeService = RememberMeService::instance ();
			$rememberMeService->deleteRememberMe ( $userId, $cookie ['token'], 'rememberme' );
		}
		$this->clearRememberMeCookie ();
	}

	/**
	 * Set the remember me cookie
	 *
	 * @param string $token
	 * @param DateTime $createdDate
	 * @param DateTime $expireDate
	 * @param int $expire
	 */
	private function setRememberMeCookie($token, \DateTime $createdDate, \DateTime $expireDate) {
		$value = json_encode ( array (
			'expire' => $expireDate->getTimestamp (),
			'created' => $createdDate->getTimestamp (),
			'token' => $token 
		) );
		setcookie ( $this->remembermeId, $value, $expireDate->getTimestamp (), Config::$a ['cookie'] ['path'], Config::$a ['cookie'] ['domain'] );
	}

	/**
	 * Return the current rememberme cookie
	 *
	 * @return array null
	 */
	private function getRememberMeCookie() {
		if (isset ( $_COOKIE [$this->remembermeId] ) && ! empty ( $_COOKIE [$this->remembermeId] )) {
			return json_decode ( $_COOKIE [$this->remembermeId], true );
		}
		return null;
	}

	/**
	 * Clear the current user remember me cookie
	 */
	private function clearRememberMeCookie() {
		if (isset ( $_COOKIE [$this->remembermeId] )) {
			unset ( $_COOKIE [$this->remembermeId] );
		}
		setcookie ( $this->remembermeId, '', time () - 3600, Config::$a ['cookie'] ['path'], Config::$a ['cookie'] ['domain'] );
	}

	/**
	 * Flag a user session for update
	 * @param int $userId
	 */
	public function flagUserForUpdate($userId) {
		$user = UserService::instance ()->getUserById ( $userId );
		$credentials = $this->getUserCredentials ( $user, 'session' );
		
		if (Session::instance () != null && Session::getCredentials ()->getUserId () == $userId) {
			// Update the current session if the userId is the same as the credential user id
			Session::updateCredentials ( $credentials );
			// Init / create the current users chat session
			ChatIntegrationService::instance ()->setChatSession ( $credentials, Session::getSessionId () );
		} else {
			// Otherwise set a session variable which is picked up by the remember me service to update the session
			$cache = Application::instance ()->getCacheDriver ();
			$cache->save ( sprintf ( 'refreshusersession-%s', $userId ), time (), intval ( ini_get ( 'session.gc_maxlifetime' ) ) );
		}
		ChatIntegrationService::instance ()->refreshChatUserSession ( $credentials );
	}

	/**
	 * Check if the user has been flagged for update
	 *
	 * @param int $userId
	 * @return last update time | false
	 */
	private function isUserFlaggedForUpdate($userId) {
		$cache = Application::instance ()->getCacheDriver ();
		$lastUpdated = $cache->fetch ( sprintf ( 'refreshusersession-%s', $userId ) );
		return ($lastUpdated && $lastUpdated != Session::get ( 'lastUpdated' )) ? $lastUpdated : false;
	}

	/**
	 * Updates the session last updated time to match the cache time
	 *
	 * @param int $userId
	 * @param int $lastUpdated
	 * @return boolean
	 */
	private function clearUserUpdateFlag($userId, $lastUpdated) {
		Session::set ( 'lastUpdated', $lastUpdated );
	}

}