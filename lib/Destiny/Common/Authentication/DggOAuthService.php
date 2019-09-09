<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Application;
use Destiny\Common\Exception;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use PDO;

/**
 * @method static DggOAuthService instance()
 */
class DggOAuthService extends Service {

    /**
     * @throws Exception
     * @throws DBALException
     */
    public function ensureAuthClient(string $clientId): array {
        $data = $this->getAuthClientByCode($clientId);
        if (empty($data)) {
            throw new Exception('Invalid client_id');
        }
        return $data;
    }

    public function saveFlashStore(string $name, array $data = [], int $lifeTime = 300 /* 5m */) {
        $cache = Application::getNsCache();
        $name = "[oauth]$name";
        $cache->save($name, \GuzzleHttp\json_encode($data), $lifeTime);
    }

    /**
     * @throws Exception
     */
    public function getFlashStore(string $name, string $identifier): array {
        $cache = Application::getNsCache();
        $name = "[oauth]$name";
        if ($cache->contains($name)) {
            return \GuzzleHttp\json_decode($cache->fetch($name), true);
        }
        throw new Exception("Invalid $identifier");
    }

    /**
     * @return array|false
     */
    public function deleteFlashStore(string $name) {
        $cache = Application::getNsCache();
        $name = "[oauth]$name";
        if ($cache->contains($name)) {
            return $cache->delete($name);
        }
        return false;
    }

    /**
     * @throws DBALException
     */
    public function addAuthClient(array $client) {
        $conn = Application::getDbConn();
        $conn->insert('oauth_client_details', [
            'clientCode' => $client['clientCode'],
            'clientSecret' => $client['clientSecret'],
            'clientName' => $client['clientName'],
            'redirectUrl' => $client['redirectUrl'],
            'ownerId' => $client['ownerId'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ], [
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            PDO::PARAM_STR,
            PDO::PARAM_INT,
            PDO::PARAM_STR,
            PDO::PARAM_STR
        ]);
    }

    /**
     * @throws DBALException
     */
    public function updateAuthClient(string $clientId, array $client) {
        $conn = Application::getDbConn();
        $auth ['modifiedDate'] = Date::getSqlDateTime();
        $conn->update('oauth_client_details', $client, ['clientId' => $clientId]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function removeAuthClient(int $clientId) {
        $conn = Application::getDbConn();
        $conn->delete('oauth_client_details', ['clientId' => $clientId]);
    }

    /**
     * @return array|null
     * @throws DBALException
     */
    public function getAuthClientById(int $clientId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_client_details
            WHERE clientId = :clientId
            LIMIT 1
        ");
        $stmt->bindValue('clientId', $clientId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @return array|null
     * @throws DBALException
     */
    public function getAuthClientByCode(string $clientCode) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_client_details
            WHERE clientCode = :clientCode
            LIMIT 1
        ");
        $stmt->bindValue('clientCode', $clientCode, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function getAuthClientsByUserId(int $userId): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("SELECT * FROM oauth_client_details WHERE ownerId = :ownerId");
        $stmt->bindValue('ownerId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array|null
     * @throws DBALException
     */
    public function getAccessTokenById(int $tokenId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.tokenId = :tokenId
            LIMIT 1");
        $stmt->bindValue('tokenId', $tokenId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function getAccessTokensByClientId(int $clientId): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.clientId = :clientId
            ORDER BY t.clientId ASC, t.tokenId DESC");
        $stmt->bindValue('clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @throws DBALException
     */
    public function getAccessTokensByUserId(int $userId): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT t.*, c.clientName FROM oauth_access_tokens t
            LEFT JOIN oauth_client_details c ON c.clientId = t.clientId
            WHERE t.userId = :userId
            ORDER BY t.clientId ASC, t.tokenId DESC");
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return array|null
     * @throws DBALException
     */
    public function getAccessTokenByRefreshAndClientId(string $refreshToken, int $clientId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_access_tokens
            WHERE refresh = :refresh AND clientId = :clientId
            LIMIT 1
        ");
        $stmt->bindValue('refresh', $refreshToken, PDO::PARAM_STR);
        $stmt->bindValue('clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @return array|null
     * @throws DBALException
     */
    public function getAccessTokenByToken(string $token) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("
            SELECT * FROM oauth_access_tokens
            WHERE token = :token
            LIMIT 1
        ");
        $stmt->bindValue('token', $token, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
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
            PDO::PARAM_INT,
            PDO::PARAM_INT,
            PDO::PARAM_STR,
            PDO::PARAM_INT,
            PDO::PARAM_STR
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function removeAccessToken(int $tokenId) {
        $conn = Application::getDbConn();
        $conn->delete('oauth_access_tokens', ['tokenId' => $tokenId]);
    }

    /**
     * @throws DBALException
     */
    public function renewAccessToken(string $accessToken, string $renewToken, int $tokenId) {
        $conn = Application::getDbConn();
        $conn->update('oauth_access_tokens', [
            'token' => $accessToken,
            'refresh' => $renewToken,
            'createdDate' => Date::getSqlDateTime()
        ], ['tokenId' => $tokenId]);
    }

    public function hasAccessTokenExpired(array $token): bool {
        $expireIn = intval($token['expireIn']);
        return $expireIn > 0 ? (Date::getDateTimePlusSeconds($token['createdDate'], $expireIn) < Date::getDateTime()) : false;
    }

    /**
     * @throws Exception
     */
    public function validateNewCodeChallenge(string $challenge) {
        if (mb_strlen($challenge) > 128) {
            throw new Exception("code_challenge must not be more than 128 characters long");
        }
        $cache = Application::getNsCache();
        if ($cache->contains("challenge[$challenge]")) {
            throw new Exception("code_challenge used too recently");
        }
        $cache->save("challenge[$challenge]", true, 30);
    }

    /**
     * @throws Exception
     */
    public function validateNewState(string $state) {
        if (mb_strlen($state) > 64) {
            throw new Exception("state must not be more than 64 characters long");
        }
    }

}