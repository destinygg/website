<?php
namespace Destiny\Chat;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Doctrine\DBAL\DBALException;

/**
 * @method static ChatBanService instance()
 */
class ChatBanService extends Service {

    /**
     * @return array
     * @throws DBALException
     */
    public function getActiveBans() {
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
            GROUP BY b.starttimestamp
            ORDER BY b.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Removes all of the bans and notifies the chat to refresh the bans
     * so it actually notices the bans being removed
     *
     * @throws DBALException
     */
    public function purgeBans() {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('TRUNCATE TABLE bans');
        $stmt->execute();
    }

}