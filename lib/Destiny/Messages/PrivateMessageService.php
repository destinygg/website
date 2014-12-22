<?php
namespace Destiny\Messages;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\User\UserService;
use Destiny\Common\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Exception;

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

    public function canSend($user, $targetuserid) {
        if ($user->hasRole(UserRole::ADMIN))
            return true;

        $userid = $user->getUserId();
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT
                userid,
                targetuserid,
                isread,
                UNIX_TIMESTAMP(timestamp) AS timestamp
            FROM privatemessages
            WHERE
                (
                    userid = :userid OR
                    targetuserid = :userid
                ) AND
                DATE_SUB(NOW(), INTERVAL 1 HOUR) < timestamp
            ORDER BY id ASC
        ");
        $stmt->bindValue('userid', $userid, \PDO::PARAM_INT);
        $stmt->execute();

        $now       = time();
        $cansend   = true;
        $timelimit = 60 * 60 * 1;
        $messagelimit = 3;
        
        $general_unread_count = 0
        $target_unread_count = 0

        while($row = $stmt->fetch()) {
            if ($row['userid'] == $userid && !$row['isread']) {
                // $userid sent a message that was NOT read
                $general_unread_count += 1;
                
                // immediatly throttle if sent more than $messagelimit unread 
                // messages to the same $targetuserid in the last $timelimit minutes
                // ONLY a reply can cancel this, otherwise it would `return false`
                if ($row['targetuserid'] != $targetuserid)
                    continue;
                
                $target_unread_count += 1
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
                // $userid recieved a message from someone unrelated to this conversation 
                $general_unread_count -= 2;
            }
        }
        // sent message count outweighs the received message count, deny
        // considering this is the last hour, and most people don't mark as read
        if ( $target_unread_count > 7 || $general_unread_count > 21 )
            $cansend = false;

        return $cansend;
    }
    
    public function getUnreadMessageCount($targetuserid) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM privatemessages AS pm
            WHERE
                targetuserid = :targetuserid AND
                isread       = 0
        ");
        $stmt->bindValue("targetuserid", $targetuserid, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
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
    public function getInboxMessagesByUserId($userid){
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            SELECT
                pm.id,
                pm.userid,
                pm.targetuserid,
                pm.message,
                pm.timestamp,
                pm.isread,
                du.username AS fromuser,
                tdu.username AS touser
            FROM privatemessages AS pm
            LEFT JOIN dfl_users AS du ON(
                du.userId = pm.userid
            )
            LEFT JOIN dfl_users AS tdu ON(
                tdu.userId = pm.targetuserid
            )
            WHERE
                pm.userid       = :userid OR
                pm.targetuserid = :userid
            ORDER BY pm.id DESC
        ");
        $stmt->bindValue('userid', $userid, \PDO::PARAM_INT);
        $stmt->execute();

        $threads = array();
        $unreadthreads = array();
        while($row = $stmt->fetch()) {
            if ($row['targetuserid'] != $userid) {
                $index = $row['targetuserid'];
                $nick  = $row['touser'];
            } else {
                $index = $row['userid'];
                $nick  = $row['fromuser'];
            }

            // since we are ordered descending, this will init the thread with
            // the latest message and timestamp
            if (!isset($threads[ $index ]))
                $threads[ $index ] = array(
                    'othernick' => $nick,
                    'timestamp' => $row['timestamp'],
                    'message'   => $row['message'],
                    'count'     => 0,
                );

            $threads[ $index ]['count']++;
            if ($row['targetuserid'] == $userid and !$row['isread'])
                $unreadthreads[ $index ] = true;
        }

        $unread = array();
        $read = array();
        foreach($threads as $threadid => $value) {
            if (isset($unreadthreads[ $threadid ]))
                $unread[ $threadid ] = $value;
            else
                $read[ $threadid ] = $value;

            unset($threads[ $threadid ]);
        }

        return array(
            'unread' => $unread,
            'read'   => $read,
        );
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
     * used for the reply feature
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
            SELECT
                p.*,
                from.username `from`
            FROM privatemessages p
            LEFT JOIN `dfl_users` `from` ON (
                from.userId = p.userid
            )
            WHERE
                p.userid IN(:userId, :targetUserId) AND
                p.targetuserid IN(:userId, :targetUserId)
            ORDER BY p.id DESC
            LIMIT :start,:limit
        ');
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('targetUserId', $targetUserId, \PDO::PARAM_INT);
        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Removes null | empty values, lowercases all usernames and removes the current username from the list.
     *
     * @throws Exception
     * @return array
     */
    public function prepareRecipients(array $recipients){
        $userService = UserService::instance();
        $userId = Session::getCredentials ()->getUserId ();

        $recipients = array_unique(array_map('strtolower', $recipients));
        if(empty($recipients)){
            throw new Exception('Invalid recipients list');
        }

        $ids = $userService->getUserIdsByUsernames($recipients);
        if(Session::hasRole(UserRole::ADMIN)){
            foreach ($recipients as $recipient) {
                switch ($recipient) {
                    case 't1 subscribers':
                        $ids += $userService->getUserIdsBySubscriptionTier(1);
                        break;

                    case 't2 subscribers':
                        $ids += $userService->getUserIdsBySubscriptionTier(2);
                        break;

                    case 't3 subscribers':
                        $ids += $userService->getUserIdsBySubscriptionTier(3);
                        break;

                    case 't4 subscribers':
                        $ids += $userService->getUserIdsBySubscriptionTier(4);
                        break;
                }
            }
        }

        if(count($ids) == 1 && $ids[0] == $userId){
            throw new Exception('Cannot send a message to yourself only.');
        }

        $recipients = array_diff($ids, array($userId));

        if(empty($recipients)){
            throw new Exception('Invalid recipient value(s)');
        }

        return $recipients;
    }

    public function markMessagesRead($targetuserid, $fromuserid) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            UPDATE privatemessages
            SET isread = 1
            WHERE
                targetuserid = :targetuserid AND
                userid       = :fromuserid
        ");
        $stmt->bindValue('targetuserid', $targetuserid, \PDO::PARAM_INT);
        $stmt->bindValue('fromuserid', $fromuserid, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function markMessageRead($messageid, $targetuserid) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare("
            UPDATE privatemessages
            SET isread = 1
            WHERE
                id           = :messageid AND
                targetuserid = :targetuserid
            LIMIT 1
        ");
        $stmt->bindValue('messageid', $messageid, \PDO::PARAM_INT);
        $stmt->bindValue('targetuserid', $targetuserid, \PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->rowCount();
    }
}
