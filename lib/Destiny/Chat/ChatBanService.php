<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\DBException;
use Destiny\Common\Service;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static ChatBanService instance()
 */
class ChatBanService extends Service {

    /**
     * @return array|false
     * @throws DBException
     */
    public function getUserActiveBan(int $userId, string $ipaddress = null) {
        try {
            $conn = Application::getDbConn();
            if(empty($ipaddress)) {
                $stmt = $conn->prepare('
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
                    b.starttimestamp < NOW() AND 
                    b.targetuserid = :userId AND
                    (b.endtimestamp > NOW() OR b.endtimestamp IS NULL)
                  GROUP BY b.targetuserid
                  ORDER BY b.id DESC
                  LIMIT 0,1
                ');
                $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            } else {
                $stmt = $conn->prepare('
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
                    b.starttimestamp < NOW() AND 
                    (b.targetuserid = :userId OR b.ipaddress = :ipaddress) AND
                    (b.endtimestamp > NOW() OR b.endtimestamp IS NULL)
                  GROUP BY b.targetuserid
                  ORDER BY b.id DESC
                  LIMIT 0,1
                ');
                $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
                $stmt->bindValue('ipaddress', $ipaddress, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error returning user ban.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function removeUserBan(int $userid): int {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("
              UPDATE bans SET endtimestamp = NOW()
              WHERE targetuserid = :targetuserid AND (endtimestamp IS NULL OR endtimestamp >= NOW())
            ");
            $stmt->bindValue('targetuserid', $userid, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (DBALException $e) {
            throw new DBException("Error removing user ban.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function insertBan(array $ban): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert('bans', $ban);
            return intval($conn->lastInsertId());
        } catch (DBALException $e) {
            throw new DBException("Error inserting user ban.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function updateBan(array $ban) {
        try {
            $conn = Application::getDbConn();
            $conn->update('bans', $ban, ['id' => $ban ['id']]);
        } catch (DBALException $e) {
            throw new DBException("Error updating user ban.", $e);
        }
    }

    /**
     * Updates all active bans for a user.
     *
     * IP bans may result in multiple active bans for a single user in the
     * `bans` table (one for each IP address they ever connected with). They
     * should always have the same `reason`, `starttimestamp`, and
     * `endtimestamp` for proper enforcement. This function provides a
     * convenient way of updating all of them at once.
     *
     * @throws DBException
     */
    public function updateActiveBansForUser(int $userId, string $reason, string $startTimestamp, string $endTimestamp = null) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              UPDATE
                bans
              SET
                reason = :reason,
                starttimestamp = :startTimestamp,
                endtimestamp = :endTimestamp
              WHERE
                targetuserid = :userId AND
                (endtimestamp IS NULL OR endtimestamp >= NOW())
            ');

            $stmt->bindValue('reason', $reason, PDO::PARAM_STR);
            $stmt->bindValue('startTimestamp', $startTimestamp, PDO::PARAM_STR);
            $stmt->bindValue('endTimestamp', $endTimestamp, PDO::PARAM_STR);
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);

            $stmt->execute();
        } catch (DBALException $e) {
            throw new DBException("Error updating active user bans.", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function getBanById(int $banId) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
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
            ');
            $stmt->bindValue('id', $banId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error updating user ban.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getActiveBans(): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare("
                SELECT
                    b.id AS banid,
                    b.starttimestamp,
                    b.endtimestamp,
                    b.reason,
                    b.ipaddress,
                    b.targetuserid AS targetuserid,
                    tu.username AS targetusername,
                    b.userid AS banninguserid,
                    u.username AS banningusername
                FROM
                    bans AS b,
                    dfl_users AS tu,
                    dfl_users AS u
                WHERE
                    (
                        b.endtimestamp IS NULL OR
                        b.endtimestamp >= NOW()
                    ) AND
                    b.userid       = u.userId AND
                    b.targetuserid = tu.userId
		GROUP BY targetuserid
                ORDER BY b.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error returning active user bans.", $e);
        }
    }

    /**
     * Removes all of the bans and notifies the chat to refresh the bans
     * so it actually notices the bans being removed
     *
     * @throws DBException
     */
    public function purgeBans() {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('TRUNCATE TABLE bans');
            $stmt->execute();
        } catch (DBALException $e) {
            throw new DBException("Error purging active user bans.", $e);
        }
    }

    /**
     * Returns all bans for a user, active and expired, sorted by ID (most
     * recent first).
     * 
     * @param int $userId ID of the banned user.
     * @param int $limit The max number of bans to get.
     *
     * @throws DBException
     */
    public function getBansForUser(int $userId, int $limit): array { 
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                  SELECT
                    b.id,
                    b.userid AS banninguserid,
                    u.username AS banningusername,
                    b.targetuserid targetuserid,
                    u2.username AS targetusername,
                    GROUP_CONCAT(b.ipaddress) as ipaddresses,
                    b.reason,
                    b.starttimestamp,
                    b.endtimestamp,
                    IF(b.endtimestamp IS NULL OR b.endtimestamp >= NOW(), 1, 0) as active
                  FROM
                    bans AS b
                    INNER JOIN dfl_users AS u ON u.userId = b.userid
                    INNER JOIN dfl_users AS u2 ON u2.userId = b.targetuserid
                  WHERE 
                    b.targetuserid = :userId
                  GROUP BY b.reason, b.starttimestamp, b.endtimestamp
                  ORDER BY b.id DESC
                  LIMIT :amount
            ');
            $stmt->bindValue('userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue('amount', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error getting bans for user.", $e);
        }
    }

}
