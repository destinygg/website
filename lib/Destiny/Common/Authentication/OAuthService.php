<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;

/**
 * @method static OAuthService instance()
 */
class OAuthService extends Service {

    /**
     * @param int $clientId
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function ensureAuthClient($clientId) {
        $data = $this->getAuthClientByCode($clientId);
        if (empty($data)) {
            throw new Exception('Invalid client_id');
        }
        return $data;
    }

    /**
     * @param string $name
     * @param array $data
     */
    public function saveFlashStore($name, array $data) {
        $cache = Application::instance()->getCache();
        $name = "[oauth]$name";
        $cache->save($name, \GuzzleHttp\json_encode($data), 3600); // TODO 300
    }

    /**
     * @param string $name
     * @param string $identifier
     * @return array
     * @throws Exception
     */
    public function getFlashStore($name, $identifier) {
        $cache = Application::instance()->getCache();
        $name = "[oauth]$name";
        if ($cache->contains($name)) {
            return \GuzzleHttp\json_decode($cache->fetch($name), true);
        }
        throw new Exception("Invalid $identifier");
    }

    /**
     * @param string $name
     * @return array | false
     */
    public function deleteFlashStore($name) {
        $cache = Application::instance()->getCache();
        $name = "[oauth]$name";
        if ($cache->contains($name)) {
            return $cache->delete($name);
        }
        return false;
    }

    /**
     * @param array $client
     */
    public function addAuthClient(array $client) {
        $conn = Application::getDbConn();
        $conn->insert('oauth_client_details', [
            'clientCode' => $client['clientCode'],
            'clientSecret' => $client['clientSecret'],
            'clientName' => $client['clientName'],
            'ownerId' => $client['ownerId'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ], [
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_INT,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR
        ]);
    }

    /**
     * @param string $clientId
     * @param array $client
     */
    public function updateAuthClient($clientId, array $client) {
        $conn = Application::getDbConn();
        $auth ['modifiedDate'] = Date::getSqlDateTime();
        $conn->update('oauth_client_details', $client, ['clientId' => $clientId]);
    }

    /**
     * @param number $clientId
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function removeAuthClient($clientId) {
        $conn = Application::getDbConn();
        $conn->delete('oauth_client_details', ['clientId' => $clientId]);
    }

    /**
     * @param int $clientId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAuthClientById($clientId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_client_details
            WHERE clientId = :clientId
            LIMIT 1
        ");
        $stmt->bindValue('clientId', $clientId, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param string $clientCode
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAuthClientByCode($clientCode) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_client_details
            WHERE clientCode = :clientCode
            LIMIT 1
        ");
        $stmt->bindValue('clientCode', $clientCode, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAuthClientsByUserId($userId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("SELECT * FROM oauth_client_details WHERE ownerId = :ownerId");
        $stmt->bindValue('ownerId', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $tokenId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAccessTokenById($tokenId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.tokenId = :tokenId
            LIMIT 1");
        $stmt->bindValue('tokenId', $tokenId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $clientId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAccessTokensByClientId($clientId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.clientId = :clientId
            ORDER BY t.clientId ASC, t.tokenId DESC");
        $stmt->bindValue('clientId', $clientId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param int $userId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAccessTokensByUserId($userId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.userId = :userId
            ORDER BY t.clientId ASC, t.tokenId DESC");
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param string $refreshToken
     * @param int $clientId
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAccessTokenByRefreshAndClientId($refreshToken, $clientId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_access_tokens
            WHERE refresh = :refresh AND clientId = :clientId
            LIMIT 1
        ");
        $stmt->bindValue('refresh', $refreshToken, \PDO::PARAM_STR);
        $stmt->bindValue('clientId', $clientId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param string $token
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAccessTokenByToken($token) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_access_tokens
            WHERE token = :token
            LIMIT 1
        ");
        $stmt->bindValue('token', $token, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @param array $data
     */
    public function addAccessToken(array $data) {
        $conn = Application::getDbConn();
        $conn->insert('oauth_access_tokens', [
            'clientId' => $data['clientId'],
            'userId' => $data['userId'],
            'token' => $data['token'],
            'refresh' => $data['refresh'],
            'scope' => $data['scope'],
            'expireIn' => $data['expireIn'],
            'createdDate' => Date::getSqlDateTime()
        ], [
            \PDO::PARAM_INT,
            \PDO::PARAM_INT,
            \PDO::PARAM_STR,
            \PDO::PARAM_INT,
            \PDO::PARAM_STR
        ]);
    }

    /**
     * @param int $tokenId
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function removeAccessToken($tokenId) {
        $conn = Application::getDbConn();
        $conn->delete('oauth_access_tokens', ['tokenId' => $tokenId]);
    }

    /**
     * @param string $accessToken
     * @param string $renewToken
     * @param int $tokenId
     */
    public function renewAccessToken($accessToken, $renewToken, $tokenId) {
        $conn = Application::getDbConn();
        $conn->update('oauth_access_tokens', [
            'token' => $accessToken,
            'refresh' => $renewToken,
            'createdDate' => Date::getSqlDateTime()
        ], ['tokenId' => $tokenId]);
    }

    /**
     * @param array $token
     * @return bool
     */
    public function checkAccessTokenExpiration($token) {
        return Date::getDateTimePlusSeconds($token['createdDate'], intval($token['expireIn'])) < Date::getDateTime();
    }

}