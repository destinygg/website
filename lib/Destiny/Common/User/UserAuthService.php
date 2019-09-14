<?php
namespace Destiny\Common\User;

use Destiny\Common\Application;
use Destiny\Common\Authentication\OAuthResponse;
use Destiny\Common\DBException;
use Destiny\Common\Exception;
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
     * @throws Exception
     */
    public function saveUserAuthWithOAuth(OAuthResponse $res, int $userId) {
        try {
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
        } catch (Exception $e) {
            throw new Exception("Error saving user auth.", $e);
        }
    }

    /**
     * TODO Feels like this method is ripe for accidental exposure of data
     * @throws DBException
     * @return int|false
     */
    public function getUserIdByAuthDetail(string $detail, string $provider) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("SELECT a.userId FROM dfl_users_auth a WHERE a.authDetail = :detail AND a.authProvider = :provider LIMIT 1");
            $stmt->bindValue('detail', $detail, PDO::PARAM_STR);
            $stmt->bindValue('provider', $provider, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * @throws DBException
     * @return int|false
     */
    public function getUserIdByAuthIdAndProvider($id, string $provider) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("SELECT a.userId FROM dfl_users_auth a WHERE a.authId = :id AND a.authProvider = :provider LIMIT 1");
            $stmt->bindValue('id', $id, PDO::PARAM_STR);
            $stmt->bindValue('provider', $provider, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getByUserId(int $userId): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId ORDER BY a.createdDate DESC');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getByAuthIdAndProvider(string $authId, string $authProvider) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.authId = :authId AND a.authProvider = :authProvider LIMIT 1');
            $stmt->bindValue('authId', $authId, PDO::PARAM_STR);
            $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getByUserIdAndAuthIdAndProvider(int $userId, string $authId, string $authProvider) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId AND a.authId = :authId AND a.authProvider = :authProvider LIMIT 1');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('authId', $authId, PDO::PARAM_STR);
            $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * TODO Not 1...* happy
     * @throws DBException
     * @return array|false
     */
    public function getByUserIdAndProvider(int $userId, string $authProvider) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT a.* FROM dfl_users_auth AS a WHERE a.userId = :userId AND a.authProvider = :authProvider LIMIT 1');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('authProvider', $authProvider, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading auth.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function removeById(int $id) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('dfl_users_auth', ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error removing auth by id.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function removeByIdAndUserId(int $id, int $userId) {
        try {
            $conn = Application::getDbConn();
            $conn->delete('dfl_users_auth', ['id' => $id, 'userId' => $userId]);
        } catch (DBALException $e) {
            throw new DBException("Error removing auth by id and user id.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function updateUserAuth(int $id, array $auth) {
        try {
            $conn = Application::getDbConn();
            $auth ['modifiedDate'] = Date::getSqlDateTime();
            $conn->update('dfl_users_auth', $auth, ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error updating auth.", $e);
        }
    }

    /**
     * @throws DBException
     */
    private function addUserAuth(array $auth) {
        try {
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
        } catch (DBALException $e) {
            throw new DBException("Error inserting auth.", $e);
        }
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