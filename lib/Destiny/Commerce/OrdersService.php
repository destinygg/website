<?php
namespace Destiny\Commerce;

use Destiny\Common\Application;
use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * @method static OrdersService instance()
 */
class OrdersService extends Service {

    /**
     * @throws DBALException
     */
    public function addIpnRecord(array $ipn) {
        $conn = Application::getDbConn();
        $conn->insert ( 'dfl_orders_ipn', [
            'ipnTrackId' => $ipn ['ipnTrackId'],
            'ipnTransactionId' => $ipn ['ipnTransactionId'],
            'ipnTransactionType' => $ipn ['ipnTransactionType'],
            'ipnData' => $ipn ['ipnData']
        ]);
    }

    /**
     * @throws DBALException
     */
    public function updatePayment(array $payment) {
        $conn = Application::getDbConn();
        $conn->update('dfl_orders_payments', $payment, ['paymentId' => $payment['paymentId']]);
    }

    /**
     * @return array|false
     * @throws DBALException
     */
    public function getPaymentByTransactionId(string $transactionId) {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1');
        $stmt->bindValue('transactionId', $transactionId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws DBALException
     */
    public function getPaymentsBySubscriptionId(int $subscriptionId, int $limit = 100, int $start = 0): array {
        $conn = Application::getDbConn();
        $stmt = $conn->prepare('
            SELECT p.* FROM dfl_orders_payments `p`
            INNER JOIN dfl_users_subscriptions `s` ON (s.subscriptionId = p.subscriptionId)
            WHERE p.subscriptionId = :subscriptionId
            ORDER BY p.paymentDate ASC
            LIMIT :start,:limit
        ');
        $stmt->bindValue('subscriptionId', $subscriptionId, PDO::PARAM_INT);
        $stmt->bindValue('start', $start, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @throws DBALException
     */
    public function addPayment(array $payment): int {
        $conn = Application::getDbConn();
        $conn->insert ( 'dfl_orders_payments', [
            'subscriptionId' => $payment ['subscriptionId'],
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