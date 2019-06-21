<?php
namespace Destiny\Commerce;
use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static DonationService instance()
 */
class DonationService extends Service {

    /**
     * @throws DBALException
     */
    public function addDonation(array $donation): array {
        $conn = Application::getDbConn();
        $conn->insert ( 'donations', $donation);
        $donation['id'] = $conn->lastInsertId ();
        return $donation;
    }

    /**
     * @throws DBALException
     */
    public function addPayment(array $payment): int {
        $conn = Application::getDbConn();
        $conn->insert ( 'dfl_orders_payments', [
            'donationId' => $payment ['donationId'],
            'amount' => $payment ['amount'],
            'currency' => $payment ['currency'],
            'transactionId' => $payment ['transactionId'],
            'transactionType' => $payment ['transactionType'],
            'paymentType' => $payment ['paymentType'],
            'payerId' => $payment ['payerId'],
            'paymentStatus' => $payment ['paymentStatus'],
            'paymentDate' => $payment ['paymentDate'],
            'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' )
        ]);
        return intval($conn->lastInsertId());
    }

    /**
     * @throws DBALException
     */
    public function updateDonation(int $id, array $donation){
        $conn = Application::getDbConn();
        $conn->update('donations', $donation, ['id' => $id]);
    }

    /**
     * @return array|false
     * @throws DBALException
     */
    public function findById(int $id) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM `donations` WHERE `id` = :id LIMIT 1');
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function findCompletedByUserId(int $userId, int $limit = 100, int $start = 0): array {
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
    }


}