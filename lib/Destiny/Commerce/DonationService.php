<?php
namespace Destiny\Commerce;
use Destiny\Common\Application;
use Destiny\Common\DBException;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static DonationService instance()
 */
class DonationService extends Service {

    /**
     * @throws DBException
     */
    public function addDonation(array $donation): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert ( 'donations', $donation);
            return intval($conn->lastInsertId());
        } catch (DBALException $e) {
            throw new DBException("Error adding donation.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function updateDonation(int $id, array $donation){
        try {
            $conn = Application::getDbConn();
            $conn->update('donations', $donation, ['id' => $id]);
        } catch (DBALException $e) {
            throw new DBException("Error updating donation", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function findById(int $id) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `id` = :id LIMIT 1');
            $stmt->bindValue('id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading donation", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function findCompletedByUserId(int $userId, int $limit = 100, int $start = 0): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
              SELECT * FROM `donations` d 
              WHERE d.`userid` = :userid AND d.`status` = :status
              ORDER BY d.`timestamp` DESC
              LIMIT :start,:limit
            ');
            $stmt->bindValue('userid', $userId, PDO::PARAM_INT);
            $stmt->bindValue('status', DonationStatus::COMPLETED, PDO::PARAM_STR);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading donation", $e);
        }
    }


}