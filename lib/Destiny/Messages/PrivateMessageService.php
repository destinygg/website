<?php
namespace Destiny\Messages;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;

class PrivateMessageService extends Service {
    
    /**
     * Singleton instance
     *
     * var UserFeaturesService
     */
    protected static $instance = null;

    /**
     * Singleton instance
     *
     * @return PrivateMessageService
     */
    public static function instance() {
        return parent::instance ();
    }
    
    /**
     * Add a new message
     *
     * @param array $data
     * @return int last_insert_id()
     */
    public function addMessage(array $data){
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'privatemessages', array (
            'userid' => $data['userid'],
            'targetuserid' => $data['targetuserid'],
            'message' => $data['message'],
            'timestamp' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) ,
            'isread' => 0
        ), array (
            \PDO::PARAM_INT,
            \PDO::PARAM_INT,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_INT
        ));
        return $conn->lastInsertId ();
    }

    /**
     * Mark a message as isread
     *
     * @param int $id
     * @return int insert_id()
     */
    public function openMessageById($id){
        $conn = Application::instance ()->getConnection ();
        $conn->update( 'privatemessages', array (
            'isread' => 1
        ), array(
            'id' => $id
        ));
    }

    /**
     * Mark all messages between user and target as isread
     *
     * @param int $userId
     * @param int $targetuserid
     * @return int affected_rows()
     */
    public function openMessagesByUserIdAndTargetUserId($userId, $targetuserid){
        $conn = Application::instance ()->getConnection ();
        return $conn->update( 'privatemessages', array (
            'isread' => 1
        ), array(
            'userId' => $userId,
            'targetuserid' => $targetuserid
        ));
    }

    /**
     * Get a list of messages by the target id and isread value
     *
     * @param int $userId
     * @param int $isread
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function getInboxMessagesByUserId($userId, $isread, $start=0, $limit=100){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT 
            f.id,
            f.userid,
            f.targetuserid,
            f.message,
            f.timestamp, 
            (
                SELECT GROUP_CONCAT(DISTINCT u.username) FROM `privatemessages` p
                LEFT JOIN `dfl_users` u ON (u.userId = p.userid)
                WHERE (p.userid = f.userid AND p.targetuserid = f.targetuserid) OR (p.userid = f.targetuserid AND p.targetuserid = f.userid)

            ) `from`,
            (
                SELECT COUNT(*) FROM `privatemessages` p
                WHERE (p.userid = f.userid AND p.targetuserid = f.targetuserid) OR (p.userid = f.targetuserid AND p.targetuserid = f.userid)

            ) `count`
            FROM (
                SELECT * FROM `privatemessages` a
                WHERE (LEAST(a.userid, a.targetuserid), GREATEST(a.userid, a.targetuserid), a.timestamp) IN (   
                    SELECT LEAST(b.userid, b.targetuserid) AS `x`, GREATEST(b.userid, b.targetuserid) AS `y`, MAX(b.timestamp)
                    FROM `privatemessages` b
                    GROUP BY `x`, `y`
                )
            ) f
            WHERE :userId IN (f.userid, f.targetuserid) 
            AND ( 
                SELECT MIN(p.isread) FROM `privatemessages` p
                WHERE p.targetuserid = :userId AND p.userid IN (f.userid, f.targetuserid) 
                LIMIT 1
            ) = :isread
            GROUP BY f.id
            ORDER BY f.timestamp DESC
            LIMIT :start,:limit
        ");
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('isread', $isread, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a single message by id and targetuserid
     *
     * @param int $id
     * @param int $targetUserId
     * @return array
     */
    public function getMessageByIdAndTargetUserId($id, $targetUserId){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare('
            SELECT p.*, from.username `from` FROM privatemessages p
            LEFT JOIN `dfl_users` `from` ON (from.userId = p.userid)
            WHERE p.id = :id AND p.targetuserid = :targetUserId
            LIMIT 0,1
        ');
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->bindValue('targetUserId', $targetUserId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a single message by id and (targetuserid or userid)
     *
     * @param int $id
     * @param int $userid
     * @return array
     */
    public function getMessageByIdAndTargetUserIdOrUserId($id, $userid) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare('
            SELECT p.*, from.username `from` FROM privatemessages p
            LEFT JOIN `dfl_users` `from` ON (from.userId = p.userid)
            WHERE p.id = :id AND (p.targetuserid = :userid OR p.userid = :userid)
            LIMIT 0,1
        ');
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);
        $stmt->bindValue('userid', $userid, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get a list of messages by userId and targetUserId
     *
     * @param int $userId
     * @param int $targetUserId
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function getMessagesBetweenUserIdAndTargetUserId($userId, $targetUserId, $start=0, $limit=50){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare('
            SELECT p.*, from.username `from` FROM privatemessages p
            LEFT JOIN `dfl_users` `from` ON (from.userId = p.userid)
            WHERE (p.userid = :userId AND p.targetuserid = :targetUserId) OR (p.userid = :targetUserId AND p.targetuserid = :userId)
            ORDER BY p.timestamp DESC
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('targetUserId', $targetUserId, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}