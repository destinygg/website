<?php
namespace Destiny\Commerce;

use Destiny\Common\Application;
use Destiny\Common\DBException;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static OrdersService instance()
 */
class OrdersService extends Service {

    /**
     * @throws DBException
     */
    public function addIpnRecord(array $ipn) {
        try {
            $conn = Application::getDbConn();
            $conn->insert ( 'dfl_orders_ipn', [
                'ipnTrackId' => $ipn ['ipnTrackId'],
                'ipnTransactionId' => $ipn ['ipnTransactionId'],
                'ipnTransactionType' => $ipn ['ipnTransactionType'],
                'ipnData' => $ipn ['ipnData']
            ]);
        } catch (DBALException $e) {
            throw new DBException("Error adding IPN record.", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function updatePayment(array $payment) {
        try {
            $conn = Application::getDbConn();
            $conn->update('dfl_orders_payments', $payment, ['paymentId' => $payment['paymentId']]);
        } catch (DBALException $e) {
            throw new DBException("Error updating payment", $e);
        }
    }

    /**
     * @return array|false
     * @throws DBException
     */
    public function getPaymentByTransactionId(string $transactionId) {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1');
            $stmt->bindValue('transactionId', $transactionId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (DBALException $e) {
            throw new DBException("Error loading payment", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function getPaymentsBySubscriptionId(int $subscriptionId, int $limit = 100, int $start = 0): array {
        try {
            $conn = Application::getDbConn();
            $stmt = $conn->prepare('
                SELECT p.* FROM dfl_orders_payments `p`
                INNER JOIN dfl_payments_purchases `s` ON (s.paymentId = p.paymentId)
                WHERE s.subscriptionId = :subscriptionId
                ORDER BY p.paymentDate ASC
                LIMIT :start,:limit
            ');
            $stmt->bindValue('subscriptionId', $subscriptionId, PDO::PARAM_INT);
            $stmt->bindValue('start', $start, PDO::PARAM_INT);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (DBALException $e) {
            throw new DBException("Error loading payment", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function addPayment(array $payment): int {
        try {
            $conn = Application::getDbConn();
            $conn->insert ( 'dfl_orders_payments', [
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
        } catch (DBALException $e) {
            throw new DBException("Error adding payment", $e);
        }
    }

    /**
     * @throws DBException
     */
    public function addPurchaseOfSubscription(int $paymentId, int $subscriptionId) {
        try {
            $conn = Application::getDbConn();
            $conn->insert('dfl_payments_purchases', [
                'paymentId' => $paymentId,
                'subscriptionId' => $subscriptionId
            ]);
        } catch (DBALException $e) {
            throw new DBException('Error adding new purchase of subscription.', $e);
        }
    }

    /**
     * @throws DBException
     */
    public function addPurchaseOfDonation(int $paymentId, int $donationId) {
        try {
            $conn = Application::getDbConn();
            $conn->insert('dfl_payments_purchases', [
                'paymentId' => $paymentId,
                'donationId' => $donationId
            ]);
        } catch (DBALException $e) {
            throw new DBException('Error adding new purchase of donation.', $e);
        }
    }

    /**
     * Returns an easier way to read a billing cycle
     */
    public function buildBillingCycleString(int $frequency, string $period): string {
        if ($frequency < 1) {
            return 'Never';
        }
        if ($frequency == 1) {
            return 'Once a ' . strtolower ( $period );
        }
        if ($frequency > 1) {
            return 'Every ' . $frequency . ' ' . strtolower ( $period ) . 's';
        }
        return '';
    }

}