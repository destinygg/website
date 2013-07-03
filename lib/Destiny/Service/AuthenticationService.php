<?php
namespace Destiny\Service;

use Destiny\Utils\Color;
use Destiny\Config;
use Destiny\Application;
use Destiny\Service\RememberMeService;
use Destiny\Utils\Http;
use Destiny\AppException;
use Destiny\Utils\Date;
use Destiny\Session;
use Destiny\Service;
use Destiny\Service\UserService;
use Destiny\Service\SubscriptionsService;
use Destiny\Service\Fantasy\TeamService;
use Destiny\SessionCredentials;
use Destiny\UserRole;

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
	 * @throws AppException
	 */
	public function validateUsername($username, array $user = null) {
		if (empty ( $username )) {
			throw new AppException ( 'Username required' );
		}
		if (preg_match ( '/^[A-Za-z0-9_]{4,20}$/', $username ) == 0) {
			throw new AppException ( 'Username may only contain A-z 0-9 or underscores and must be over 3 characters and under 20 characters in length.' );
		}
		if (preg_match_all ( '/[0-9]{4}/', $username, $m ) > 0) {
			throw new AppException ( 'Too many numbers in a row' );
		}
		if (preg_match_all ( '/[\_]{2}/', $username, $m ) > 0 || preg_match_all ( "/[_]+/", $username, $m ) > 2) {
			throw new AppException ( 'Too many underscores' );
		}
		if (preg_match_all ( "/[0-9]/", $username, $m ) > round ( strlen ( $username ) / 2 )) {
			throw new AppException ( 'Number ratio is too damn high' );
		}
		if (! empty ( $user )) {
			if (UserService::instance ()->getIsUsernameTaken ( $username, $user ['userId'] )) {
				throw new AppException ( 'The username you asked for is already being used' );
			}
		} else {
			if (UserService::instance ()->getIsUsernameTaken ( $username )) {
				throw new AppException ( 'The username you asked for is already being used' );
			}
		}
	}

	/**
	 * Validate email
	 *
	 * @param string $email
	 * @param array $user
	 * @throws AppException
	 */
	public function validateEmail($email, array $user = null) {
		if (! filter_var ( $email, FILTER_VALIDATE_EMAIL )) {
			throw new AppException ( 'A valid email is required' );
		}
		if (! empty ( $user )) {
			if (UserService::instance ()->getIsEmailTaken ( $email, $user ['userId'] )) {
				throw new AppException ( 'The email you asked for is already being used' );
			}
		} else {
			if (UserService::instance ()->getIsEmailTaken ( $email )) {
				throw new AppException ( 'The email you asked for is already being used' );
			}
		}
	}

	/**
	 * Logout a user
	 */
	public function logout() {
		$userId = Session::getCredentials ()->getUserId ();
		if (! empty ( $userId )) {
			$this->clearRememberMe ( $userId );
		}
		Session::destroy ();
	}

	/**
	 * Setup the authenticated user
	 *
	 * @param array $user
	 */
	public function login(array $user, $authProvider) {
		$session = Session::instance ();
		// Renew the session upon successful login, makes it slightly harder to hijack
		$session->renew ( true );
		
		// Check the user status
		if (strcasecmp ( $user ['userStatus'], 'Active' ) !== 0) {
			throw new AppException ( sprintf ( 'User status not active. Status: %s', $user ['userStatus'] ) );
		}
		
		$credentials = new SessionCredentials ();
		$credentials->setUserId ( $user ['userId'] );
		$credentials->setUserName ( $user ['username'] );
		$credentials->setEmail ( $user ['email'] );
		$credentials->setCountry ( $user ['country'] );
		$credentials->setAuthProvider ( $authProvider );
		$credentials->setUserStatus ( $user ['userStatus'] );
		$credentials->addRoles ( UserRole::USER );
		
		// Get the users active subscriptions
		$subscription = SubscriptionsService::instance ()->getUserActiveSubscription ( $user ['userId'] );
		if (! empty ( $subscription )) {
			$credentials->addRoles ( UserRole::SUBSCRIBER );
		}
		
		// Get the stored roles
		$credentials->addRoles ( UserService::instance ()->getUserRoles ( $user ['userId'] ) );
		
		// Add the user features
		$credentials->setFeatures ( UserFeaturesService::instance ()->getUserFeatures ( $user ['userId'] ) );
		
		// Generate the user color
		$credentials->setColor ( Color::getFeaturesColor ( $credentials->getFeatures () ) );
		
		// Update the auth credentials
		Session::updateCredentials ( $credentials );
		
		// @TODO find a better place for this
		// If this user has no team, create a new one
		$team = TeamService::instance ()->getTeamByUserId ( $user ['userId'] );
		if (empty ( $team )) {
			$team = array ();
			$team ['teamId'] = TeamService::instance ()->addTeam ( $user ['userId'], Config::$a ['fantasy'] ['team'] ['startCredit'], Config::$a ['fantasy'] ['team'] ['startTransfers'] );
		}
		Session::set ( 'teamId', $team ['teamId'] );
	}

	/**
	 * Validate that a set of auth credentials has what it needs
	 *
	 * @param array $authCreds
	 * @throws AppException
	 */
	private function validateAuthCredentials(array $authCreds) {
		if (! isset ( $authCreds ['authId'] ) || ! isset ( $authCreds ['username'] ) || ! isset ( $authCreds ['email'] ) || ! isset ( $authCreds ['authCode'] ) || ! isset ( $authCreds ['authProvider'] )) {
			Application::instance ()->getLogger ()->error ( sprintf ( 'Error validating auth credentials %s', var_dump ( $authCreds ) ) );
			throw new AppException ( 'Invalid auth credentials' );
		}
	}

	/**
	 * Handles the credentials after authorization
	 *
	 * @param string $accessToken
	 * @param array $authCreds
	 */
	public function handleAuthCredentials(array $authCreds) {
		$userService = UserService::instance ();
		// Make sure the credentials are valid
		$this->validateAuthCredentials ( $authCreds );
		$profileUser = $userService->getUserByAuthId ( $authCreds ['authId'], $authCreds ['authProvider'] );
		
		// Check if we are MERGING or not
		if (Session::get ( 'accountMerge' ) == 1) {
			if (! Session::hasRole ( \Destiny\UserRole::USER )) {
				throw new AppException ( 'Authentication required' );
			}
			$sessAuth = Session::getCredentials ()->getData ();
			// We need to merge the accounts if one exists
			if (! empty ( $profileUser )) {
				// If the profile userId is the same as the current one, the profiles are connceted, they shouldnt be here
				if ($profileUser ['userId'] == $sessAuth ['userId']) {
					throw new AppException ( 'These account are already connected' );
				}
				// If the profile user is older than the current user, prompt the user to rather login using the other profile
				if (intval ( $profileUser ['userId'] ) < $sessAuth ['userId']) {
					throw new AppException ( sprintf ( 'Your user profile for the %s account is older. Please login and use that account to merge.', $authCreds ['authProvider'] ) );
				}
				// So we have a profile for a different user to the one logged in, we delete that user, and add a profile for the current user
				$userService->removeAuthProfile ( $profileUser ['userId'], $authCreds ['authProvider'] );
				// Set the user profile to Merged
				$userService->updateUser ( $profileUser ['userId'], array (
					'userStatus' => 'Merged' 
				) );
			}
			$userService->addUserAuthProfile ( array (
				'userId' => $sessAuth ['userId'],
				'authProvider' => $authCreds ['authProvider'],
				'authId' => $authCreds ['authId'],
				'authToken' => $authCreds ['authCode'] 
			) );
			Session::set ( 'accountMerge' );
			Http::header ( Http::HEADER_LOCATION, '/profile/authentication' );
			exit ();
		}
		
		// If the user is empty stop and go to confirm / setup the user details
		if (empty ( $profileUser )) {
			Session::set ( 'authSession', $authCreds );
			Http::header ( Http::HEADER_LOCATION, '/register?code=' . urlencode ( $authCreds ['authCode'] ) );
			exit ();
		}
		
		// Update the auth profile for this provider
		$authProfile = $userService->getUserAuthProfile ( $profileUser ['userId'], $authCreds ['authProvider'] );
		if (! empty ( $authProfile )) {
			$userService->updateUserAuthProfile ( $profileUser ['userId'], $authCreds ['authProvider'], array (
				'authToken' => $authCreds ['authCode'] 
			) );
		}
		
		// Login, setup user session
		$this->login ( $profileUser, $authCreds ['authProvider'] );
		if (Session::set ( 'rememberme' )) {
			$this->setRememberMe ( $profileUser );
		}
		
		Session::set ( 'authSession' );
		Http::header ( Http::HEADER_LOCATION, '/' );
		exit ();
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
						throw new AppException ( 'Token invalid [createdDate] does not match' );
					}
					if (Date::getDateTime ( $rememberMe ['expireDate'] ) != Date::getDateTime ( $cookie ['expire'] )) {
						throw new AppException ( 'Token invalid [expireDate] does not match' );
					}
					if ($cookie ['token'] != md5 ( $rememberMe ['userId'] . Date::getDateTime ( $rememberMe ['createdDate'] )->getTimestamp () . Date::getDateTime ( $rememberMe ['expireDate'] )->getTimestamp () . $this->remembermeSalt )) {
						throw new AppException ( 'Token invalid [token] does not match' );
					}
				} catch ( AppException $e ) {
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
	private function setRememberMeCookie($token,\DateTime $createdDate,\DateTime $expireDate) {
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

}