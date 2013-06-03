<?php
namespace Destiny;

use Destiny\Service\Fantasy\Db\Team;

abstract class Session {
	
	public static $authorized = false;
	public static $sessionId = '';
	public static $token = '';
	public static $userId = '';
	public static $user = array ();
	public static $team = array ();
	
	/**
	 * @var SessionStorage
	 */
	protected static $storage = null;

	public static function init() {
		session_set_cookie_params ( Config::$a ['cookie'] ['life'], '/', Config::$a ['cookie'] ['domain'] );
		session_name ( Config::$a ['cookie'] ['name'] );
		session_start ();
		self::$storage = new SessionStorage ();
		self::setSessionId ( session_id () );
		if (self::get ( 'authorized' ) === true) {
			self::loadSession ();
		}
	}

	private static function loadSession() {
		$data = self::$storage->loadSession ();
		if (! empty ( $data )) {
			self::setUser ( $data );
			self::setToken ( $data ['token'] );
			self::setUserId ( $data ['userId'] );
			self::setAuthorized ( ($data ['authorized'] == '1') ? true : false );
			// @TODO This seems overkill here - happens on every request
			self::setTeam ( Team::getInstance ()->getTeamByUserId ( $data ['userId'] ) );
		}
	}

	public static function persist() {
		session_regenerate_id ( self::getSessionId () );
		self::setSessionId ( session_id () );
		self::$storage->persist ();
	}

	public static function garbageCollect() {
		$db = Application::getInstance ()->getDb ();
		$db->query ( 'DELETE FROM `dfl_users_sessions` WHERE expireDate < UTC_TIMESTAMP()' );
	}

	public static function destroy() {
		self::$storage->destroy ();
		session_destroy ();
		session_regenerate_id ();
	}

	public static function get($name) {
		return (isset ( $_SESSION [$name] )) ? $_SESSION [$name] : null;
	}

	public static function set($name, $value) {
		$_SESSION [$name] = $value;
	}

	public static function hasRole($roleName) {
		$user = self::getUser ();
		if (! empty ( $user ) && ! empty ( $user [$roleName] ) && $user [$roleName] == '1') {
			return true;
		}
		return false;
	}

	public static function getAuthorized() {
		return self::$authorized;
	}

	public static function setAuthorized($authorized) {
		self::$authorized = ( boolean ) $authorized;
		self::set ( 'authorized', $authorized );
	}

	public static function getUser() {
		return self::$user;
	}

	public static function setUser(array $user) {
		self::$user = $user;
	}

	public static function getToken() {
		return self::$token;
	}

	public static function setToken($token) {
		self::$token = $token;
	}

	public static function getSessionId() {
		return self::$sessionId;
	}

	public static function setSessionId($sessionId) {
		self::$sessionId = $sessionId;
	}

	public static function getUserId() {
		return Session::$userId;
	}

	public static function setUserId($userId) {
		Session::$userId = $userId;
	}
	
	public static function setTeam(array $team){
		Session::$team = $team;
	}
	
	public static function getTeam(){
		return Session::$team;
	}

}
class SessionStorage {

	public function loadSession() {
		$db = Application::getInstance ()->getDb ();
		return $db->select ( '
			SELECT 
				sess.token,
				sess.authorized,
				users.*,
				IF(subs.userId IS NULL,0,1) AS `subscriber`,
				subs.createdDate AS `subCreatedDate`,
				subs.endDate AS `subEndDate`
			FROM `dfl_users_sessions` AS `sess` 
			INNER JOIN `dfl_users` AS `users` ON (users.userId = sess.userId) 
			LEFT JOIN dfl_users_subscriptions AS `subs` ON (subs.userId = users.userId AND subs.endDate > NOW() AND subs.active = 1) 
			WHERE sess.sessionId = \'{sessionId}\'
			ORDER BY users.createdDate DESC
			LIMIT 0,1
			', array (
				'sessionId' => Session::getSessionId () 
		) )->fetchRow ();
	}

	public function persist() {
		$db = Application::getInstance ()->getDb ();
		$count = $db->select ( 'SELECT COUNT(*) FROM `dfl_users_sessions` WHERE sessionId = \'' . Session::getSessionId () . '\'' )->fetchValue ();
		if (( int ) $count == 0) {
			// This seems overkill, but it keeps the duplicate records clean
			$db->query ( 'DELETE FROM `dfl_users_sessions` WHERE userId = \'{userId}\'', array (
					'userId' => Session::getUserId () 
			) );
			$db->query ( '
					INSERT INTO `dfl_users_sessions` SET 
						`userId` = \'{userId}\',
						`sessionId` = \'{sessionId}\',
						`authorized` = \'{authorized}\',
						`token` = \'{token}\',
						`createdDate` = UTC_TIMESTAMP(),
						`modifiedDate` = UTC_TIMESTAMP(),
						`expireDate` = UTC_TIMESTAMP() + INTERVAL 1 WEEK 
				', array (
					'userId' 		=> Session::getUserId (),
					'sessionId' 	=> Session::getSessionId (),
					'authorized' 	=> ((Session::getAuthorized ()) ? '1' : '0'),
					'token' 		=> Session::getToken () 
			) );
		} else {
			$db->query ( '
					UPDATE `dfl_users_sessions` SET 
					authorized = \'{authorized}\',
					token = \'{token}\',
					modifiedDate = UTC_TIMESTAMP(),
					expireDate = UTC_TIMESTAMP() + INTERVAL 1 WEEK  
					WHERE sessionId = \'{sessionId}\' AND userId = \'{userId}\'
				', array (
					'userId' 		=> Session::getUserId (),
					'sessionId' 	=> Session::getSessionId (),
					'authorized' 	=> ((Session::getAuthorized ()) ? '1' : '0'),
					'token' 		=> Session::getToken () 
			) );
		}
	}

	public function destroy() {
		$db = Application::getInstance ()->getDb ();
		$db->query ( 'DELETE FROM `dfl_users_sessions` WHERE sessionId = \'{sessionId}\'', array (
				'sessionId' => Session::getSessionId () 
		) );
	}

}