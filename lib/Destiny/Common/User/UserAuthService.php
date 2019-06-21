<?php
namespace Destiny\Common\User;

use Destiny\Common\Application;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static UserAuthService instance()
 */
class UserAuthService extends Service {

    /**
     * Saves the UserAuth based on the properties on the oauth response
     * If there is an existing UserAuth record, update it, else add it
     *
     * Im assuming if we are at this point
     * The auth is owned by the user, so if it is attached to a different dgg user,
     * we remove it before adding it to this $userId.
     *
     * @throws DBALException
     */
    public function saveUserAuthWithOAuth(OAuthResponse $res, int $userId) {
        $userAuth = $this->getByUserIdAndAuthIdAndProvider($userId, $res->getAuthId(), $res->getAuthProvider());
        if (!empty($userAuth)) {
            $this->updateUserAuth($userAuth['id'], $this->authResponseToUserAuth($res, $userId));
        } else {
            // TODO AUDIT
            $userAuth = $this->getByAuthIdAndProvider($res->getAuthId(), $res->getAuthProvider());
            if (!empty($userAuth)) {
                $this->removeById($userAuth['id']);
            }
            $this->addUserAuth($this->authResponseToUserAuth($res, $userId));
        }
    }

    /**
     * TODO Feels like this method is ripe for accidental exposure of data
     * @throws DBALException
     * @return int|false
     */
    public function getUserIdByAuthDetail(string $detail, string $provider) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("SELECT a.userId FROM dfl_users_auth a WHERE a.authDetail = :detail AND a.authProvider = :provider LIMIT 1");
        $stmt->bindValue('detail', $detail, PDO::PARAM_STR);
        $stmt->bindValue('provider', $provider, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @throws DBALException
     * @return int|false
     */
    public function getUserIdByAuthIdAndProvider($id, string $provider) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare("SELECT a.userId FROM dfl_users_auth a WHERE a.authId = :id AND a.authProvider = :provider LIMIT 1");
        $stmt->bindValue('id', $id, PDO::PARAM_STR);
        $stmt->bindValue('provider', $provider, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @throws DBALException
     */
    public function getByUserId(int $userId): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId ORDER BY a.createdDate DESC');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @throws DBALException
     */
    public function getByAuthIdAndProvider(string $authId, string $authProvider) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.authId = :authId AND a.authProvider = :authProvider LIMIT 1');
        $stmt->bindValue('authId', $authId, PDO::PARAM_STR);
        $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function getByUserIdAndAuthIdAndProvider(int $userId, string $authId, string $authProvider) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId AND a.authId = :authId AND a.authProvider = :authProvider LIMIT 1');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('authId', $authId, PDO::PARAM_STR);
        $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * TODO Not 1...* happy
     * @throws DBALException
     * @return array|false
     */
    public function getByUserIdAndProvider(int $userId, string $authProvider) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId AND a.authProvider = :authProvider LIMIT 1');
        $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function removeById(int $id) {
        $conn = Application::getDbConn();
        $conn->delete('dfl_users_auth', ['id' => $id]);
    }

    /**
     * @throws DBALException
     */
    public function removeByIdAndUserId(int $id, int $userId) {
        $conn = Application::getDbConn();
        $conn->delete('dfl_users_auth', ['id' => $id, 'userId' => $userId]);
    }

    /**
     * @throws DBALException
     */
    public function updateUserAuth(int $id, array $auth) {
        $conn = Application::getDbConn();
        $auth ['modifiedDate'] = Date::getSqlDateTime();
        $conn->update('dfl_users_auth', $auth, ['id' => $id]);
    }

    /**
     * @throws DBALException
     */
    private function addUserAuth(array $auth) {
        $conn = Application::getDbConn();
        $conn->insert('dfl_users_auth', [
            'userId' => $auth['userId'],
            'authId' => $auth['authId'],
            'authProvider' => $auth['authProvider'],
            'authEmail' => $auth['authEmail'],
            'authDetail' => $auth['authDetail'],
            'accessToken' => $auth['accessToken'],
            'refreshToken' => $auth['refreshToken'],
            'createdDate' => Date::getSqlDateTime(),
            'modifiedDate' => Date::getSqlDateTime()
        ]);
    }

    /**
     * @return array
     */
    private function authResponseToUserAuth(OAuthResponse $auth, int $userId): array {
        return [
            'userId' => $userId,
            'authId' => $auth->getAuthId(),
            'authProvider' => $auth->getAuthProvider(),
            'authEmail' => $auth->getAuthEmail(),
            'authDetail' => $auth->getAuthDetail(),
            'accessToken' => $auth->getAccessToken(),
            'refreshToken' => $auth->getRefreshToken(),
        ];
    }
}