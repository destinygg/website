<?php
namespace Destiny\Common\Authentication;

use Destiny\Common\Service;
use Destiny\Common\Application;

/**
 * @method static RememberMeService instance()
 */
class RememberMeService extends Service {
    
    /**
     * @return void
     */
    public function clearExpiredRememberMe() {
        $conn = Application::instance ()->getConnection ();
        $conn->executeQuery ( 'DELETE FROM dfl_users_rememberme WHERE expireDate <= NOW()' );
    }

    /**
     * @param int $userId
     * @param string $token
     * @param string $tokenType
     */
    public function deleteRememberMe($userId, $token, $tokenType) {
        $conn = Application::instance ()->getConnection ();
        $conn->delete ( 'dfl_users_rememberme', array (
            'userId' => $userId,
            'token' => $token,
            'tokenType' => $tokenType 
        ) );
    }

    /**
     * @param int $userId
     * @param string $token
     * @param string $tokenType
     * @param \DateTime $expire
     * @param \DateTime $createdDate
     */
    public function addRememberMe($userId, $token, $tokenType, \DateTime $expire, \DateTime $createdDate) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_users_rememberme', array (
            'userId' => $userId,
            'token' => $token,
            'tokenType' => $tokenType,
            'createdDate' => $createdDate->format ( 'Y-m-d H:i:s' ),
            'expireDate' => $expire->format ( 'Y-m-d H:i:s' ) 
        ), array (
            \PDO::PARAM_INT,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR 
        ) );
    }

    /**
     * Get the [userId, createdDate, expireDate] for the associated token
     *
     * @param string $token
     * @param string $tokenType
     * @return array
     */
    public function getRememberMe($token, $tokenType) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT u.userId,r.createdDate,r.expireDate FROM dfl_users_rememberme AS r
            INNER JOIN dfl_users AS u ON (u.userId = r.userId)
            WHERE r.token = :token AND r.tokenType = :tokenType AND r.expireDate > NOW() 
            LIMIT 0,1
        ' );
        $stmt->bindValue ( 'token', $token, \PDO::PARAM_STR );
        $stmt->bindValue ( 'tokenType', $tokenType, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

}