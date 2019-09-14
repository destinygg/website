<?php
namespace Destiny\Messages;

use Destiny\Common\Application;
use Destiny\Common\DBException;
use Destiny\Common\Service;
use Destiny\Common\Session\SessionCredentials;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use PDO;

/**
 * @method static PrivateMessageService instance()
 */
class PrivateMessageService extends Service {

    /**
     * Check if a user is allowed to send a message based on various criteria
     * @throws DBException
     */
    public function canSend(SessionCredentials $user, int $targetuserid): bool {
        if ($user->hasRole(UserRole::ADMIN))
            return true;

        $userid    = $user->getUserId();
        $now       = time();
        $cansend   = true;
        $timelimit = 60 * 60 * 1;
        $messagelimit = 3;
        
        $general_unread_count = 0;
        $target_unread_count = 0;

        $stmt = $this->getMessageTimeAndReadData($userid);
        while($row = $stmt->fetch()) {
            if ($row['userid'] == $userid && !$row['isread']) {
                // $userid sent a message that was NOT read
                $general_unread_count += 1;
                
                // immediately throttle if sent more than $messagelimit unread
                // messages to the same $targetuserid in the last $timelimit minutes
                // ONLY a reply can cancel this, otherwise it would `return false`
                if ($row['targetuserid'] != $targetuserid)
                    continue;
                
                $target_unread_count += 1;
                if($target_unread_count > $messagelimit && $now - $row['timestamp'] < $timelimit)
                    $cansend = false;
                
            } else if ( $row['userid'] == $targetuserid ) {
                $target_unread_count -= $messagelimit;
                $general_unread_count -= $messagelimit;
                $cansend = true;
                
                // avoid rate limiting quick replies
                // received a message in the last $timelimit minutes, reset
                if ($now - $row['timestamp'] < $timelimit)
                    return true;
            }else {
                // $userid sent a message that was read OR
                // $userid received a message from someone unrelated to this conversation
                $general_unread_count -= 2;
            }
        }
        // sent message count outweighs the received message count, deny
        // considering this is the last hour, and most people don't mark as read
        if ( $target_unread_count > 7 || $general_unread_count > 21 )
            $cansend = false;

        return $cansend;
    }

    /**
     * @throws DBException
     */
    private function getMessageTimeAndReadData(int $userid): Statement {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("
                SELECT userid, targetuserid, isread, UNIX_TIMESTAMP(timestamp) AS timestamp
                FROM privatemessages 
                WHERE (userid = :userid OR targetuserid = :userid) 
                AND DATE_SUB(NOW(), INTERVAL 1 HOUR) < timestamp
                ORDER BY id ASC
            ");
            $stmt->bindValue('userid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (DBALException $e) {
            throw new DBException("Error loading messages.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function addMessage(array $data): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert ( 'privatemessages', [
                'userid' => $data['userid'],
                'targetuserid' => $data['targetuserid'],
                'message' => $data['message'],
                'timestamp' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) ,
                'isread' => 0,
                'deletedbysender' => 0,
                'deletedbyreceiver' => 0,
            ], [
                PDO::PARAM_INT,
                PDO::PARAM_INT,
                PDO::PARAM_STR,
                PDO::PARAM_STR,
                PDO::PARAM_INT
            ]);
            return intval($conn->lastInsertId());
        } catch (DBALException $e) {
            throw new DBException("Error inserting message.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getMessagesInboxByUserId(int $userid, int $start = 0, int $limit = 20): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("
                SELECT z.*,pm3.message FROM (
                    SELECT
                        MAX(pm.id) `id`,
                        MAX(pm.timestamp) `timestamp`,
                        IF(pm.targetuserid = :userid, du.userId, tdu.userId) AS `userid`,
                        IF(pm.targetuserid = :userid, du.username, tdu.username) AS `user`,
                        SUM(IF(pm.isread=0 AND pm.targetuserid = :userid,1,0)) `unread`,
                        SUM(IF(pm.isread=1 AND pm.targetuserid = :userid,1,0)) `read`
                    FROM privatemessages AS pm
                    LEFT JOIN dfl_users AS du ON(du.userId = pm.userid)
                    LEFT JOIN dfl_users AS tdu ON(tdu.userId = pm.targetuserid)
                    WHERE (pm.userid = :userid AND pm.deletedbysender = 0) 
                    OR (pm.targetuserid = :userid AND pm.deletedbyreceiver = 0) 
                    GROUP BY IF(pm.targetuserid = :userid, du.userId, tdu.userId) 
                ) z
                LEFT JOIN privatemessages AS pm3 ON (pm3.id = z.id)
                ORDER BY `unread` DESC, z.timestamp DESC
                LIMIT :start,:limit
            ");
            $stmt->bindValue('userid', $userid, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading messages.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getMessagesBetweenUserIdAndTargetUserId(int $userId, int $targetUserId, int $start=0, int $limit=50): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT
                    p.*,
                    `from`.username `from`,
                    `target`.username `to`
                FROM privatemessages p
                LEFT JOIN `dfl_users` AS `from` ON (`from`.userId = p.userid)
                LEFT JOIN `dfl_users` AS `target` ON (`target`.userId = p.targetuserid)
                WHERE
                    (p.userid = :userId AND p.targetuserid = :targetUserId AND p.deletedbysender = 0) OR 
                    (p.userid = :targetUserId AND p.targetuserid = :userId AND p.deletedbyreceiver = 0)
                ORDER BY p.id DESC
                LIMIT :start, :limit
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('targetUserId', $targetUserId, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading messages.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getUnreadConversations(int $userId, int $limit): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("
                SELECT * FROM (
                  SELECT p.id `messageid`, u.username,MAX(p.timestamp) `timestamp`, COUNT(*) `unread` FROM `privatemessages` p
                  INNER JOIN `dfl_users` u ON (u.userId = p.userid)
                  WHERE p.targetuserid = :userId AND p.isread = 0 AND p.deletedbyreceiver = 0
                  GROUP BY p.userid
                ) b
                ORDER BY b.timestamp DESC, b.unread DESC
                LIMIT 0,:limit
            ");
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading messages.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function markAllMessagesRead(int $targetuserid): bool {
        try {
            $conn = Application::getDbConn();
            $conn->update('privatemessages', ['isread' => 1], [
                'targetuserid' => $targetuserid,
            ], [PDO::PARAM_INT, PDO::PARAM_INT]);
            return true;
        } catch (DBALException $e) {
            throw new DBException("Error marking messages as read.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function markMessagesRead(int $targetuserid, int $fromuserid): bool {
        try {
            $conn = Application::getDbConn();
            $conn->update('privatemessages', ['isread' => 1], [
                'targetuserid' => $targetuserid,
                'userid' => $fromuserid
            ], [PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT]);
            return true;
        } catch (DBALException $e) {
            throw new DBException("Error marking messages as read.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function markMessageRead(int $messageid, int $targetuserid): bool {
        try {
            $conn = Application::getDbConn();
            $conn->update('privatemessages', ['isread' => 1], [
                'id' => $messageid,
                'targetuserid' => $targetuserid
            ], [PDO::PARAM_INT, PDO::PARAM_INT, PDO::PARAM_INT]);
            return true;
        } catch (DBALException $e) {
            throw new DBException("Error reading conversation.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function markConversationDeleted(int $userid, int $targetuserid): bool {
        try {
            $conn = Application::getDbConn();
            $conn->beginTransaction();
            // user -> target
            $stmt = $conn->prepare("
                UPDATE privatemessages pm 
                SET pm.deletedbysender = 1 
                WHERE pm.userid = :userid 
                AND pm.targetuserid = :targetuserid
            ");
            $stmt->bindValue('userid', $userid, PDO::PARAM_INT);
            $stmt->bindValue('targetuserid', $targetuserid, PDO::PARAM_INT);
            $stmt->execute();
            // user <- target
            $stmt = $conn->prepare("
                UPDATE privatemessages pm 
                SET pm.deletedbyreceiver = 1 
                WHERE pm.userid = :targetuserid
                AND pm.targetuserid = :userid 
            ");
            $stmt->bindValue('userid', $userid, PDO::PARAM_INT);
            $stmt->bindValue('targetuserid', $targetuserid, PDO::PARAM_INT);
            $stmt->execute();
            //
            $conn->commit();
            return true;
        } catch (DBALException $e) {
            throw new DBException("Error deleting conversation.", $e);
        }
    }

}
