<?php
namespace Destiny\Common\User;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Doctrine\DBAL\Connection;

/**
 * @method static UserService instance()
 */
class UserService extends Service {

  /**
   * @var array
   */
  protected $roles;

  /**
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
   * @param int $userId
   * @param string $roleName
   * @return int
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
   * @param int $userId
   */
  public function removeAllUserRoles($userId) {
    $conn = Application::instance ()->getConnection ();
    $conn->delete ( 'dfl_users_roles', array (
      'userId' => $userId
    ) );
  }

  /**
   * @param string $username
   * @param int $excludeUserId
   * @return bool
   * @throws \Doctrine\DBAL\DBALException
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
   * @param $email
   * @param int|string $excludeUserId
   * @return bool
   * @throws \Doctrine\DBAL\DBALException
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
   * @param int $username
   * @return mixed
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getUserByUsername($username) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE username = :username LIMIT 0,1' );
    $stmt->bindValue ( 'username', $username, \PDO::PARAM_STR );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * @param string $userId
   * @return mixed
   */
  public function getUserById($userId) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( 'SELECT * FROM `dfl_users` WHERE userId = :userId LIMIT 0,1' );
    $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
    $stmt->execute ();
    return $stmt->fetch ();
  }

  /**
   * @param array $user
   * @return string
   */
  public function addUser(array $user) {
    $conn = Application::instance ()->getConnection ();
    $user ['createdDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $user ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->insert ( 'dfl_users', $user );
    return $conn->lastInsertId ();
  }

  /**
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
   * @param string $authId
   * @param string $authProvider
   * @return mixed
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
   * @param $authId
   * @param $authProvider
   * @return bool
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getUserAuthProviderExists($authId, $authProvider) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT COUNT(*) FROM dfl_users_auth AS a
      INNER JOIN dfl_users AS u ON (u.userId = a.userId)
      WHERE a.authId = :authId AND a.authProvider = :authProvider
      LIMIT 1
    ' );
    $stmt->bindValue ( 'authId', $authId, \PDO::PARAM_STR );
    $stmt->bindValue ( 'authProvider', $authProvider, \PDO::PARAM_STR );
    $stmt->execute ();
    return ($stmt->fetchColumn () > 0) ? true : false;
  }

  /**
   * @param int $userId
   * @param string $authProvider
   * @return array
   * @throws \Doctrine\DBAL\DBALException
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
   * @param int $userId
   * @param string $authProvider
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
      \PDO::PARAM_STR,
      \PDO::PARAM_STR
    ) );
  }

  /**
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
   * @param string $username
   * @param int $limit
   * @param int $start
   * @return array
   * @throws \Doctrine\DBAL\DBALException
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
   * @param int $limit
   * @param int $page
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getUsers($limit, $page = 1) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT SQL_CALC_FOUND_ROWS u.userId,u.username,u.email,u.createdDate
      FROM dfl_users AS u
      ORDER BY u.userId DESC
      LIMIT :start,:limit
    ' );
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
   * @param int $limit
   * @param $page
   * @param string $search
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
  public function searchUsers($limit, $page, $search) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare ( '
      SELECT SQL_CALC_FOUND_ROWS u.userId,u.username,u.email,u.createdDate FROM dfl_users AS u
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
   * @param int $userId
   * @param int $limit
   * @param int $start
   * @return mixed
   * @throws \Doctrine\DBAL\DBALException
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
   * @param array $address
   */
  public function updateAddress(array $address){
    $conn = Application::instance ()->getConnection ();
    $address ['modifiedDate'] = Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' );
    $conn->update ( 'users_address', $address, array ('id' => $address['id']) );
  }


  /**
   * @param int $userId
   * @param string $ipaddress
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getUserActiveBan($userId, $ipaddress = "") {
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
   * @param array $ban
   * @return string
   */
  public function insertBan(array $ban) {
    $conn = Application::instance ()->getConnection ();
    $conn->insert ( 'bans', $ban );
    return $conn->lastInsertId ();
  }

  /**
   * @param $userid
   * @return int $count The number of rows modified
   * @throws \Doctrine\DBAL\DBALException
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
   * @param int $userid
   * @return array $users The users found
   * @throws Exception
   */
  public function findSameIPUsers( $userid ) {
    $keys = $this->callRedisScript('check-sameip-users', array( $userid ) );
    return $this->getUsersFromRedisKeys('CHAT:userips-', $keys );
  }

  /**
   * @param string $ipaddress
   * @return array $users The users found
   */
  public function findUsersWithIP( $ipaddress ) {
    $keys = $this->callRedisScript('check-ip', array( $ipaddress ) );
    return $this->getUsersFromRedisKeys('CHAT:userips-', $keys );
  }

  /**
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
   * @param string $keyprefix
   * @param array $keys
   * @return array $users The users found
   * @throws Exception
   * @throws \Doctrine\DBAL\DBALException
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
   * @param array $argument
   * @return array $users The users found
   * @throws Exception
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
      array(Connection::PARAM_STR_ARRAY)
    );
    $ids = array();
    $result = $stmt->fetchAll();
    foreach($result as $item) {
      $ids[] = $item['userId'];
    }
    return $ids;
  }

  /**
   * @param $userId
   * @return bool
   * @throws \Doctrine\DBAL\DBALException
   */
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

  /**
   * @param $nick
   * @return bool|string
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getTwitchIDFromNick( $nick ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT ua.authId
      FROM dfl_users_auth AS ua
      WHERE
        ua.authProvider = 'twitch' AND
        ua.authDetail   = :nick
      LIMIT 1
    ");
    $stmt->bindValue('nick', $nick, \PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
  }

  /**
   * Expects an array with twitch users _id as keys and a 0 or 1 as value
   * to indicate whether the user is a subscriber or not
   * Returns an array of active subscribers (for announcing) with the
   * key being the authid and the value being an array of user info(userid, username)
   *
   * @param array $data
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Exception
   */
  public function updateTwitchSubscriptions( array $data ) {
    if ( empty( $data ) )
      return array();

    $conn = Application::instance ()->getConnection ();
    $batchsize = 100;

    $ids = array();
    foreach( $data as $authid => $subscriber ) {
      if ( !ctype_alnum( $authid ) )
        throw new \Exception("Non alpha-numeric authid found: $authid");

      $ids[]= $authid;
    }

    // we get the users connected to the twitch authids so that later we can
    // update the users in batches efficiently and return the subs with
    // the required information to the caller
    // we default to a 1 for the istwitchsubscriber field because that is
    // assumed to be the most common case, and we will need to do less work
    // to later update the info for the nonsubs
    $idToUser = array();
    $infosql  = "
      SELECT
        u.username,
        u.userId,
        ua.authId,
        '1' AS istwitchsubscriber
      FROM
        dfl_users_auth AS ua,
        dfl_users AS u
      WHERE
        u.userId        = ua.userId AND
        ua.authProvider = 'twitch' AND
        ua.authId       IN('%s')
    ";

    // do it in moderate batches
    $chunks = array_chunk($ids, $batchsize);
    foreach($chunks as $chunk) {
      $sql  = sprintf( $infosql, implode("', '", $chunk ) );
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      while($row = $stmt->fetch())
        $idToUser[ $row['authId'] ] = $row;
    }
    unset($ids);

    if ( empty( $idToUser ) )
      return array();

    $subs    = array();
    $nonsubs = array();
    foreach( $idToUser as $authid => $user ) {
      if ( $data[ $authid ] )
        $subs[] = $user['userId'];
      else
        $nonsubs[] = $user['userId'];
    }

    $subsql = "
      UPDATE dfl_users AS u
      SET u.istwitchsubscriber = %s
      WHERE u.userId IN('%s')
    ";

    // update the subs first
    $chunks = array_chunk($subs, $batchsize);
    foreach($chunks as $chunk) {
      $sql  = sprintf( $subsql, '1', implode("', '", $chunk ) );
      $stmt = $conn->prepare($sql);
      $stmt->execute();
    }

    // update nonsubs
    $chunks = array_chunk($nonsubs, $batchsize);
    foreach($chunks as $chunk) {
      $sql  = sprintf( $subsql, '0', implode("', '", $chunk ) );
      $stmt = $conn->prepare($sql);
      $stmt->execute();
    }
    unset($subs, $nonsubs, $chunks);

    // update the return data
    foreach( $data as $authid => $subscriber) {
      if (!$subscriber)
        $idToUser[ $authid ]['istwitchsubscriber'] = '0';
    }

    return $idToUser;
  }

  /**
   * @return array
   * @throws \Doctrine\DBAL\DBALException
   */
  public function getActiveTwitchSubscriptions() {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT ua.authId
      FROM
        dfl_users AS u,
        dfl_users_auth AS ua
      WHERE
        u.userId             = ua.userId AND
        u.istwitchsubscriber = 1
    ");
    $stmt->execute();

    $ret = array();
    while($row = $stmt->fetch())
      $ret[] = $row['authId'];

    return $ret;
  }

  public function setMinecraftUUID( $userid, $uuid ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      UPDATE dfl_users
      SET minecraftuuid = :uuid
      WHERE
        userId = :userid AND
        (minecraftuuid IS NULL OR minecraftuuid = '')
      LIMIT 1
    ");
    $stmt->bindValue('userid', $userid, \PDO::PARAM_INT);
    $stmt->bindValue('uuid', $uuid, \PDO::PARAM_STR);
    $stmt->execute();
    return (bool) $stmt->rowCount();
  }

  public function getUserIdFromMinecraftUUID( $uuid ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT userId
      FROM dfl_users
      WHERE minecraftuuid = :uuid
      LIMIT 1
    ");
    $stmt->bindValue('uuid', $uuid, \PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchColumn();
  }

  public function getUserIdFromMinecraftName( $name ) {
    $conn = Application::instance ()->getConnection ();
    $stmt = $conn->prepare("
      SELECT userId
      FROM dfl_users
      WHERE minecraftname = :name
      LIMIT 1
    ");
    $stmt->bindValue('name', $name, \PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchColumn();
  }
}
