<?php
namespace Destiny\Common\User;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Config;
use Destiny\Common\Exception;

class UserService extends Service {
  
  /**
   * Singleton instance
   *
   * var UserService
   */
  protected static $instance = null;

  /**
   * Singleton instance
   *
   * @return UserService
   */
  public static function instance() {
    return parent::instance ();
  }

  /**
   * Set a list of user roles
   *
   * @param int $userId
   * @param array $roles
   */
  public function setUserRoles($userId, array $roles) {
    $this->removeAllUserRoles ( $userId );
    foreach ( $roles as $role ) {
      $this->addUserRole ( $userId, $role );
    }
  }
  
  /**
   * A list of roles
   * @var array
   */
  protected $roles;

  /**
   * Get all the user roles
   * @return array
   */
  public function getUserRoles() {
    if (! $this->roles) {
      $conn = Application::instance ()->getConnection ();
      $stmt = $conn->prepare ( 'SELECT * FROM `dfl_roles`' );
      $stmt->execute ();
      $this->roles = $stmt->fetchAll ();
    }
    return $this->roles;
  }

  /**
   * Get the roleId by roleName
   * @param string $roleName
   * @return array
   */
  public function getRoleIdByName($roleName) {
    $roles = $this->getUserRoles ();
    foreach ( $roles as $role ) {
      if (strcasecmp ( $role ['roleName'], $roleName ) === 0) {
        return $role ['roleId'];
      }
    }
    return null;
  }

  /**
   * Add a role to a user
   *
   * @param int $userId
   * @param string $roleName
   * @return the specfic record id
   */
  public function addUserRole($userId, $roleName) {
    $roleId = $this->getRoleIdByName ( $roleName );
    $conn = Application::instance ()->getConnection ();
    $conn->insert ( 'dfl_users_roles', array (
      'userId' => $userId,
      'roleId' => $roleId 
    ) );
    return $conn->lastInsertId ();
  }

  /**
   * Remove all roles from a user
   *
   * @param int $userId
   */
  public function removeAllUserRoles($userId) {
    $conn = Application::instance ()->getConnection ();
    $conn->delete ( 'dfl_users_roles', array (
      'userId' => $userId 
    ) );
  }

  /**
   * Return true if the $username has already been used, false otherwise.
   *
   * @param string $username
   * @return boolean
   */
  public function getIsUsernameTaken($username, $excludeUserId = 0) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT COUNT(*) FROM `dfl_users` WHERE username = :username AND userId != :excludeUserId AND userStatus IN (\'Active\',\'Suspended\',\'Inactive\')');
    $stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
    $stmt->bindValue ( 'excludeUserId', $excludeUserId, \PDO::PARAM_INT );
    $stmt->execute ();
    return ($stmt->fetchColumn () > 0) ? true : false;
  }

  /**
   * Return true if the $email has already been used, false otherwise.
   *
   * @param string $username
   * @param string $excludeUserId
   * @return boolean
   */
  public function getIsEmailTaken($email, $excludeUserId = 0) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT COUNT(*) FROM `dfl_users` WHERE email = :email AND userId != :excludeUserId AND userStatus IN (\'Active\',\'Suspended\',\'Inactive\')' );
    $stmt->bindValue ( 'email', $email, \PDO::PARAM_STR );
    $stmt->bindValue ( 'excludeUserId', $excludeUserId, \PDO::PARAM_INT );
    $stmt->execute ();
    return ($stmt->fetchColumn () > 0) ? true : false;
  }

  /**
   * Get the user record by username
   *
   * @param string $userId
   */
  public function getUserByUsername($username) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE username = :username LIMIT 0,1' );
    $stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * Get the user record by userId
   *
   * @param string $userId
   */
  public function getUserById($userId) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE userId = :userId LIMIT 0,1' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * Add a new user
   *
   * @param array $user
   */
  public function addUser(array $user) {
    $conn = Application::instance ()->getConnection ();
    $user ['createdDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $user ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->insert ( 'dfl_users', $user );
    return $conn->lastInsertId ();
  }

  /**
   * Update a user record
   *
   * @param int $userId
   * @param array $user
   */
  public function updateUser($userId, array $user) {
    $conn = Application::instance ()->getConnection ();
    $user ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->update ( 'dfl_users', $user, array (
      'userId' => $userId 
    ) );
  }

  /**
   * Return a list of the users roles
   *
   * @param int $userId
   * @return array
   */
  public function getUserRolesByUserId($userId) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT b.roleName FROM dfl_users_roles AS a 
      INNER JOIN dfl_roles b ON (b.roleId = a.roleId)
      WHERE a.userId = :userId
    ' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    $stmt->execute ();
    $roles = array ();
    while ( $role = $stmt->fetchColumn () ) {
      $roles [] = $role;
    }
    return $roles;
  }

  /**
   * Get the user record by external Id
   *
   * @param string $authId
   * @param string $authProvider
   */
  public function getUserByAuthId($authId, $authProvider) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT u.* FROM dfl_users_auth AS a 
      INNER JOIN dfl_users AS u ON (u.userId = a.userId)
      WHERE a.authId = :authId AND a.authProvider = :authProvider 
      LIMIT 0,1
    ' );
    $stmt->bindValue ( 'authId', $authId, \PDO::PARAM_STR );
    $stmt->bindValue ( 'authProvider', $authProvider, \PDO::PARAM_STR );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * Return a users auth profile
   *
   * @param string $authId
   * @param string $authProvider
   * @return array
   */
  public function getUserAuthProfile($userId, $authProvider) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT a.* FROM dfl_users_auth AS a 
      WHERE a.userId = :userId AND a.authProvider = :authProvider 
      LIMIT 0,1
    ' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    $stmt->bindValue ( 'authProvider', $authProvider, \PDO::PARAM_STR );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * Get all the profiles for a specific uer
   *
   * @param int $userId
   * @return array
   */
  public function getAuthProfilesByUserId($userId) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT a.* FROM dfl_users_auth AS a 
      WHERE a.userId = :userId
    ' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetchAll ();
  }

  /**
   * Updates a users auth profile
   *
   * @param array $auth
   */
  public function updateUserAuthProfile($userId, $authProvider, array $auth) {
    $conn = Application::instance ()->getConnection ();
    $auth ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->update ( 'dfl_users_auth', $auth, array (
      'userId' => $userId,
      'authProvider' => $authProvider 
    ) );
  }

  /**
   * Add a auth profile to a user
   *
   * @param array $auth
   * @return void
   */
  public function addUserAuthProfile(array $auth) {
    $conn = Application::instance ()->getConnection ();
    $conn->insert ( 'dfl_users_auth', array (
      'userId' => $auth ['userId'],
      'authProvider' => $auth ['authProvider'],
      'authId' => $auth ['authId'],
      'authCode' => $auth ['authCode'],
      'authDetail' => $auth ['authDetail'],
      'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ),
      'modifiedDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
    ), array (
      \PDO::PARAM_INT,
      \PDO::PARAM_STR,
      \PDO::PARAM_INT,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR 
    ) );
  }

  /**
   * Remove auth profile
   *
   * @param int $userId
   * @param string $authProvider
   */
  public function removeAuthProfile($userId, $authProvider) {
    $conn = Application::instance ()->getConnection ();
    $conn->delete ( 'dfl_users_auth', array (
      'userId' => $userId,
      'authProvider' => $authProvider 
    ) );
  }

  /**
   * Find a user by username, returns a list of users
   *
   * @param string $username
   * @return array
   */
  public function findUsersByUsername($username, $limit = 10, $start = 0) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT u.userId,u.username,u.email FROM dfl_users AS u 
      WHERE u.username LIKE :username
      ORDER BY u.username DESC
      LIMIT :start,:limit
    ' );
    $stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
    $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
    $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetchAll ();
  }

  /**
   * List users
   * 
   * @param int $limit
   * @param int $start
   * @param array $filters
   * @return array
   */
  public function listUsers($limit, $page = 1) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT SQL_CALC_FOUND_ROWS u.userId,u.username,u.email,s.subscriptionType,u.createdDate,s.recurring,s.status 
      FROM dfl_users AS u
      LEFT JOIN dfl_users_subscriptions AS s ON (u.userId = s.userId AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource)
      ORDER BY u.userId DESC
      LIMIT :start,:limit
    ' );
    $stmt->bindValue ( 'subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
    $stmt->bindValue ( 'subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR );
    $stmt->bindValue ( 'start', ($page-1)*$limit, \PDO::PARAM_INT );
    $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
    $stmt->execute ();
    $pagination = array ();
    $pagination ['list'] = $stmt->fetchAll ();
    $pagination ['total'] = $conn->fetchColumn ( 'SELECT FOUND_ROWS()' );
    $pagination ['totalpages'] = ceil($pagination ['total'] / $limit);
    $pagination ['pages'] = 5;
    $pagination ['page'] = $page;
    $pagination ['limit'] = $limit;
    return $pagination;
  }
  
  /**
   * Find a user by search string
   * @TODO Complicated order query to emulate "relavency"
   *
   * @param string $string          
   * @param number $limit         
   * @param number $start         
   */
  public function findUsers($string, $limit = 10, $start = 0) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT u.userId,u.username,u.email FROM dfl_users AS u 
      WHERE u.username LIKE :wildcard1 OR email LIKE :wildcard1
      ORDER BY CASE 
      WHEN u.username LIKE :wildcard2 THEN 0
      WHEN u.username LIKE :wildcard3 THEN 1
      WHEN u.username LIKE :wildcard4 THEN 2
      ELSE 3
      END, u.username
      LIMIT :start,:limit
    ' );
    $stmt->bindValue ( 'wildcard1', '%' . $string . '%', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard2', $string . ' %', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard3', $string . '%', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard4', '% %' . $string . '% %', \PDO::PARAM_STR );
    $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
    $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetchAll ();
  }

  /**
   * List users
   * 
   * @param int $limit
   * @param int $start
   * @param string $search
   * @param array $filters
   * @return array
   */
  public function searchUsers($limit, $page, $search) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT SQL_CALC_FOUND_ROWS u.userId,u.username,u.email,s.subscriptionType,u.createdDate,s.recurring,s.status FROM dfl_users AS u
      LEFT JOIN dfl_users_subscriptions AS s ON (u.userId = s.userId AND s.status = :subscriptionStatus AND s.subscriptionSource = :subscriptionSource)
      WHERE u.username LIKE :wildcard1 OR email LIKE :wildcard1
      ORDER BY CASE 
      WHEN u.username LIKE :wildcard2 THEN 0
      WHEN u.username LIKE :wildcard3 THEN 1
      WHEN u.username LIKE :wildcard4 THEN 2
      ELSE 3
      END, u.username
      LIMIT :start,:limit
    ' );

    $stmt->bindValue ( 'wildcard1', '%' . $search . '%', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard2', $search . ' %', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard3', $search . '%', \PDO::PARAM_STR );
    $stmt->bindValue ( 'wildcard4', '% %' . $search . '% %', \PDO::PARAM_STR );
    $stmt->bindValue ( 'subscriptionStatus', SubscriptionStatus::ACTIVE, \PDO::PARAM_STR );
    $stmt->bindValue ( 'subscriptionSource', Config::$a ['subscriptionType'], \PDO::PARAM_STR );
    $stmt->bindValue ( 'start', ($page-1)*$limit, \PDO::PARAM_INT );
    $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
    $stmt->execute ();
    
    $pagination = array ();
    $pagination ['list'] = $stmt->fetchAll ();
    $pagination ['total'] = $conn->fetchColumn ( 'SELECT FOUND_ROWS()' );
    $pagination ['totalpages'] = ceil($pagination ['total'] / $limit);
    $pagination ['pages'] = 5;
    $pagination ['page'] = $page;
    $pagination ['limit'] = $limit;
    return $pagination;
  }
  
  /**
   * Get a user address by user
   *
   * @param number $userId          
   * @param number $limit         
   * @param number $start         
   */
  public function getAddressByUserId($userId, $limit = 1, $start = 0) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT * FROM users_address AS a 
      WHERE a.userId = :userId
      LIMIT :start,:limit
    ' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_STR );
    $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
    $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetch ();
  }
  
  /**
   * Add a user address
   * 
   * @param array $address
   */
  public function addAddress(array $address){
    $conn = Application::instance ()->getConnection ();
    $conn->insert ( 'users_address', 
    array (
      'userId'       => $address ['userId'],
      'fullName'     => $address ['fullName'],
      'line1'        => $address ['line1'],
      'line2'        => $address ['line2'],
      'city'         => $address ['city'],
      'region'       => $address ['region'],
      'zip'          => $address ['zip'],
      'country'      => $address ['country'],
      'createdDate'  => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ),
      'modifiedDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
    ), array (
      \PDO::PARAM_INT,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR,
      \PDO::PARAM_STR 
    ) );
  }
  
  /**
   * Update a user address
   * 
   * @param array $address
   */
  public function updateAddress(array $address){
    $conn = Application::instance ()->getConnection ();
    $address ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->update ( 'users_address', $address, array ('id' => $address['id']) );
  }
  

  /**
   * Returns a list of bans
   *
   * @return array
   */
  public function getUserActiveBan($userId, $ipaddress = null) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT
        b.id,
        b.userid,
        u.username,
        b.targetuserid,
        u2.username AS targetusername,
        b.ipaddress,
        b.reason,
        b.starttimestamp,
        b.endtimestamp
      FROM
        bans AS b
        INNER JOIN dfl_users AS u ON u.userId = b.userid
        INNER JOIN dfl_users AS u2 ON u2.userId = b.targetuserid
      WHERE
        (
          b.targetuserid = :userId
          ' . ( $ipaddress? 'OR b.ipaddress = :ipaddress': '' ) . '
        ) AND
        b.starttimestamp < NOW() AND
        (
          b.endtimestamp > NOW() OR
          b.endtimestamp IS NULL
        )
      GROUP BY b.targetuserid
      ORDER BY b.id DESC
      LIMIT 0,1
    ' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    if ( $ipaddress )
      $stmt->bindValue ( 'ipaddress', $ipaddress, \PDO::PARAM_STR );
    $stmt->execute ();
    return $stmt->fetch ();
  }
  
  /**
   * Get a chat ban by ID
   *
   * @param int $banId
   * @return array
   */
  public function getBanById($banId) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT
        b.id,
        b.userid,
        u.username,
        b.targetuserid,
        u2.username AS targetusername,
        b.ipaddress,
        b.reason,
        b.starttimestamp,
        b.endtimestamp
      FROM
        bans AS b
        INNER JOIN dfl_users AS u ON u.userId = b.userid
        INNER JOIN dfl_users AS u2 ON u2.userId = b.targetuserid
      WHERE b.id = :id
      ORDER BY b.id DESC
      LIMIT 0,1
    ' );
    $stmt->bindValue ( 'id', $banId, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetch ();
  }
  
  /**
   * update an existing ban
   * @param array $ban
   */
  public function updateBan(array $ban) {
    $conn = Application::instance ()->getConnection ();
    $conn->update ( 'bans', $ban, array (
        'id' => $ban ['id']
    ) );
  }
  
  /**
   * Insert a new chat ban
   * @param array $ban
   */
  public function insertBan(array $ban) {
    $conn = Application::instance ()->getConnection ();
    $conn->insert ( 'bans', $ban );
    return $conn->lastInsertId ();
  }

  /**
   * Get a chat ban by ID
   *
   * @param int $userId
   * @return int $count The number of rows modified
   */
  public function removeUserBan( $userid ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( "
      UPDATE bans
      SET endtimestamp = NOW()
      WHERE
        targetuserid = :targetuserid AND
        (
          endtimestamp IS NULL OR
          endtimestamp >= NOW()
        )
    ");
    $stmt->bindValue ( 'targetuserid', $userid, \PDO::PARAM_INT );
    $stmt->execute();
    return $stmt->rowCount();
  }

  /**
   * Find users with the same IP as this user
   *
   * @param int $userId
   * @return array $users The users found
   */
  public function findSameIPUsers( $userid ) {
    $redis   = Application::instance ()->getRedis ();
    $keys    = $this->callRedisScript('check-sameip-users', array( $userid ) );
    return $this->getUsersFromRedisKeys('CHAT:userips-', $keys );
  }

  /**
   * Find users with the given IP
   *
   * @param string $ipaddress
   * @return array $users The users found
   */
  public function findUsersWithIP( $ipaddress ) {
    $redis   = Application::instance ()->getRedis ();
    $keys    = $this->callRedisScript('check-ip', array( $ipaddress ) );
    return $this->getUsersFromRedisKeys('CHAT:userips-', $keys );
  }

  /**
   * Find the addresses of the user
   *
   * @param int $userid
   * @return array $ipaddresses The addresses found
   */
  public function getIPByUserId( $userid ) {
    $redis   = Application::instance ()->getRedis ();
    return $redis->zrange('CHAT:userips-' . $userid, 0, -1);
  }

  /**
   * Get the users from the given redis keys, strip off the beginning of the keys
   * and parse the remaining string into an int, CHAT:userips-123 will be
   * transformed into (int)123 and than later users with the given ids
   * queried from the database ordered by username in ascending order
   *
   * @param string $ipaddress
   * @return array $users The users found
   */
  private function getUsersFromRedisKeys( $keyprefix, $keys ) {
    $userids = array();

    foreach( $keys as $key ) {
      $id = intval( substr( $key, strlen( $keyprefix ) ) );
      if ( !$id )
        throw new Exception("Invalid id: $id from key: $key");

      $userids[] = $id;
    }
    
    if ( empty( $userids ) )
      return $userids;

    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT
        userId,
        username,
        email,
        createdDate
      FROM dfl_users
      WHERE userId IN('" . implode("', '", $userids ) . "')
      ORDER BY username
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /**
   * Loads the given redis script if needed and calls it with the $arguments param
   *
   * @param string $scriptname
   * @param array $arguments
   * @return array $users The users found
   */
  private function callRedisScript( $scriptname, $argument ) {
    $redis = Application::instance ()->getRedis ();
    
    $dir   = Config::$a ['redis'] ['scriptdir'];
    $hash  = @file_get_contents( $dir . $scriptname . '.hash' );

    if ( $hash ) {
      $ret = $redis->evalSha( $hash, $argument );
      if ( $ret ) return $ret;
    }

    $hash = $redis->script('load', file_get_contents( $dir . $scriptname . '.lua' ) );
    if ( !$hash )
      throw new Exception('Unable to load script');

    if ( !file_put_contents( $dir . $scriptname . '.hash', $hash ) )
      throw new Exception('Unable to save hash');

    return $redis->evalSha( $hash, $argument );
  }

  /**
   * Get a list of user ids from a list of usernames
   *
   * @param array $usernames
   * @return array
   */
  public function getUserIdsByUsernames(array $usernames) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->executeQuery("
      SELECT u.userId FROM `dfl_users` u
      WHERE u.username IN (?)
    ", 
      array($usernames), 
      array(\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
    );
    $ids = array();
    $result = $stmt->fetchAll();
    foreach($result as $item) {
      $ids[] = $item['userId'];
    }    
    return $ids;
  }

  public function isUserOldEnough( $userId ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT COUNT(*)
      FROM dfl_users AS u
      WHERE
        u.userId = :userId AND
        DATE_ADD(u.createdDate, INTERVAL 7 DAY) < NOW()
      LIMIT 1
    ");
    $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
    $stmt->execute();
    return !!$stmt->fetchColumn();
  }
}