<?php
namespace Destiny\Commerce;

use Destiny\Common\Service;
use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Doctrine\DBAL\DBALException;

/**
 * @method static OrdersService instance()
 */
class OrdersService extends Service {

    /**
     * @param array $ipn
     *
     * @throws DBALException
     */
    public function addIpnRecord(array $ipn) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders_ipn', array (
            'ipnTrackId' => $ipn ['ipnTrackId'],
            'ipnTransactionId' => $ipn ['ipnTransactionId'],
            'ipnTransactionType' => $ipn ['ipnTransactionType'],
            'ipnData' => $ipn ['ipnData']
        ) );
    }

    /**
     * @param array $payment
     *
     * @throws DBALException
     */
    public function updatePayment(array $payment) {
        $conn = Application::instance ()->getConnection ();
        $conn->update ( 'dfl_orders_payments', $payment, array ('paymentId' => $payment['paymentId']) );
    }

    /**
     * @param string $transactionId
     * @return mixed
     *
     * @throws DBALException
     */
    public function getPaymentByTransactionId($transactionId) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_orders_payments WHERE transactionId = :transactionId LIMIT 0,1' );
        $stmt->bindValue ( 'transactionId', $transactionId, \PDO::PARAM_STR );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * @todo this returns payments in ASC order, the getPaymentsByUser returns them in DESC order
     *
     * @param int $subscriptionId
     * @param int $limit
     * @param int $start
     * @return array
     *
     * @throws DBALException
     */
    public function getPaymentsBySubscriptionId($subscriptionId, $limit = 100, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT p.* FROM dfl_orders_payments `p`
            INNER JOIN dfl_users_subscriptions `s` ON (s.subscriptionId = p.subscriptionId)
            WHERE p.subscriptionId = :subscriptionId
            ORDER BY p.paymentDate ASC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'subscriptionId', $subscriptionId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
     * @param array $payment
     * @return int paymentId
     *
     * @throws DBALException
     */
    public function addPayment(array $payment) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_orders_payments', array (
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
        ) );
        return $conn->lastInsertId ();
    }

    /**
     * Returns an easier way to read a billing cycle
     *
     * @param int $frequency
     * @param string $period
     * @return string
     */
    public function buildBillingCycleString($frequency, $period) {
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