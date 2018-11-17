<?php
namespace Destiny\Api;

use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Common\Service;
use Destiny\Common\Utils\RandomStringGenerator;
use Doctrine\DBAL\DBALException;

class ApiAuthenticationService extends Service {

    /**
     * @var RandomStringGenerator
     */
    public $generator;

    /**
     * @return ApiAuthenticationService
     */
    public static function instance() {
        $instance = parent::instance();
        $instance->generator = new RandomStringGenerator();
        return $instance;
    }

    /**
     * @param array $user
     * @return string
     * @throws \Exception
     */
    public function createAuthToken(array $user) {
        return $this->addAuthToken($user['userId'], $this->generator->generate(32));
    }

    /**
     * @param int $userId
     * @param string $token
     */
    public function addAuthToken($userId, $token) {
        $conn = Application::getDbConn();
        $conn->insert('dfl_users_auth_token', [
            'userId' => $userId,
            'authToken' => $token,
            'createdDate' => Date::getDateTime('NOW')->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param int $id
     * @throws DBALException
     */
    public function removeAuthToken($id) {
        $conn = Application::getDbConn();
        $conn->delete('dfl_users_auth_token', ['authTokenId' => $id]);
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $start
     * @return array
     * @throws DBALException
     */
    public function getAuthTokensByUserId($userId, $limit = 5, $start = 0) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT * FROM dfl_users_auth_token WHERE userId = :userId
            ORDER BY createdDate DESC
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $authTokenId
     * @return array
     * @throws DBALException
     */
    public function getAuthTokenById($authTokenId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM dfl_users_auth_token WHERE authTokenId = :authTokenId LIMIT 1');
        $stmt->bindValue('authTokenId', $authTokenId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param string $authToken
     * @return array
     * @throws DBALException
     */
    public function getAuthTokenByToken($authToken) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM dfl_users_auth_token WHERE authToken = :authToken LIMIT 1');
        $stmt->bindValue('authToken', $authToken, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

}